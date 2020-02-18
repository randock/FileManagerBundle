<?php

namespace Artgris\Bundle\FileManagerBundle\Controller;

use Symfony\Component\Validator\Constraints\Regex;
use Artgris\Bundle\FileManagerBundle\Event\Delete\File\PostDeleteFileEvent;
use Artgris\Bundle\FileManagerBundle\Event\Delete\File\PreDeleteFileEvent;
use Artgris\Bundle\FileManagerBundle\Event\Delete\Folder\PostDeleteFolderEvent;
use Artgris\Bundle\FileManagerBundle\Event\Delete\Folder\PreDeleteFolderEvent;
use Artgris\Bundle\FileManagerBundle\Event\Move\File\PostMoveFileEvent;
use Artgris\Bundle\FileManagerBundle\Event\Move\File\PreMoveFileEvent;
use Artgris\Bundle\FileManagerBundle\Event\Move\Folder\PostMoveFolderEvent;
use Artgris\Bundle\FileManagerBundle\Event\Move\Folder\PreMoveFolderEvent;
use Artgris\Bundle\FileManagerBundle\Event\Rename\PostRenameEvent;
use Artgris\Bundle\FileManagerBundle\Event\Rename\PreRenameEvent;
use Artgris\Bundle\FileManagerBundle\Event\Upload\PostUploadEvent;
use Artgris\Bundle\FileManagerBundle\Event\Upload\PreUploadEvent;
use Artgris\Bundle\FileManagerBundle\Helpers\File;
use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;
use Artgris\Bundle\FileManagerBundle\Helpers\UploadHandler;
use Artgris\Bundle\FileManagerBundle\Twig\OrderExtension;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
use Artgris\Bundle\FileManagerBundle\service\FileTypeService;

/**
 * @author Arthur Gribet <a.gribet@gmail.com>
 */
class ManagerController extends AbstractController
{
    private const ITERATION_LIMIT = 20;

    /**
     * @var FileManager
     */
    protected $fileManager;

    /**
     * @Route("/", name="file_manager")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function indexAction(Request $request)
    {
        $queryParameters = $request->query->all();
        $translator = $this->get('translator');
        $isJson = $request->get('json') ? true : false;
        if ($isJson) {
            unset($queryParameters['json']);
        }
        $fileManager = $this->newFileManager($queryParameters);

        // Folder search
        $directoriesArbo = $this->retrieveSubDirectories($fileManager, $fileManager->getDirName(), DIRECTORY_SEPARATOR, $fileManager->getBaseName());

        // File search
        $finderFiles = new Finder();
        $finderFiles->in($fileManager->getCurrentPath())->depth(0);
        $regex = $fileManager->getRegex();

        $orderBy = $fileManager->getQueryParameter('orderby');
        $orderDESC = OrderExtension::DESC === $fileManager->getQueryParameter('order');
        if (!$orderBy) {
            $finderFiles->sortByType();
        }

        switch ($orderBy) {
            case 'name':
                $finderFiles->sort(function (SplFileInfo $a, SplFileInfo $b) {
                    return strcmp(strtolower($b->getFilename()), strtolower($a->getFilename()));
                });
                break;
            case 'date':
                $finderFiles->sortByModifiedTime();
                break;
            case 'size':
                $finderFiles->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
                    return $a->getSize() - $b->getSize();
                });
                break;
        }

        if ($fileManager->getTree()) {
            $finderFiles->files()->name($regex)->filter(function (SplFileInfo $file) {
                return $file->isReadable();
            });
        } else {
            $finderFiles->filter(function (SplFileInfo $file) use ($regex) {
                if ('file' === $file->getType()) {
                    if (preg_match($regex, $file->getFilename())) {
                        return $file->isReadable();
                    }

                    return false;
                }

                return $file->isReadable();
            });
        }

        $formDelete = $this->createDeleteForm()->createView();
        $fileArray = [];
        foreach ($finderFiles as $file) {
            $fileArray[] = new File($file, $this->get('translator'), $this->get('file_type_service'), $fileManager);
        }

        if ('dimension' === $orderBy) {
            usort($fileArray, function (File $a, File $b) {
                $aDimension = $a->getDimension();
                $bDimension = $b->getDimension();
                if ($aDimension && !$bDimension) {
                    return 1;
                }

                if (!$aDimension && $bDimension) {
                    return -1;
                }

                if (!$aDimension && !$bDimension) {
                    return 0;
                }

                return ($aDimension[0] * $aDimension[1]) - ($bDimension[0] * $bDimension[1]);
            });
        }

        if ($orderDESC) {
            $fileArray = array_reverse($fileArray);
        }

        $parameters = [
            'confType' => $fileManager->getQueryParameter('conf'),
            'fileManager' => $fileManager,
            'fileArray' => $fileArray,
            'formDelete' => $formDelete,
        ];

        if ($isJson) {
            $fileList = $this->renderView('@ArtgrisFileManager/views/_manager_view.html.twig', $parameters);

            return new JsonResponse(['data' => $fileList, 'badge' => $finderFiles->count(), 'treeData' => $directoriesArbo]);
        }
        $parameters['treeData'] = json_encode($directoriesArbo);

        $form = $this->get('form.factory')->createNamedBuilder('rename', FormType::class)
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Regex(['pattern' => '/^[A-Za-z0-9][A-Za-z0-9 -_]*[A-Za-z0-9]$/'])
                ],
                'attr' => [
                    'title' => $this->get('translator')->trans('randock.ypsa.medialibrary.name.pattern.restrictions'),
                    'pattern' => '^[A-Za-z0-9][A-Za-z0-9 -_]*[A-Za-z0-9]$'
                ],
                'label' => false,
                'data' => $translator->trans('input.default'),
            ])
            ->add('send', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
                'label' => $translator->trans('button.save'),
            ])
            ->getForm();

        /* @var Form $form */
        $form->handleRequest($request);
        /** @var Form $formRename */
        $formRename = $this->createRenameForm();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $fs = new Filesystem();
            $directory = $directorytmp = $fileManager->getCurrentPath().DIRECTORY_SEPARATOR.$data['name'];
            $i = 1;

            while ($fs->exists($directorytmp) && $i < self::ITERATION_LIMIT) {
                $directorytmp = "{$directory} ({$i})";
                ++$i;
            }
            $directory = $directorytmp;

            try {
                $fs->mkdir($directory);
                $this->addFlash('success', $translator->trans('folder.add.success'));
            } catch (IOExceptionInterface $e) {
                $this->addFlash('danger', $translator->trans('folder.add.danger', ['%message%' => $data['name']]));
            }

            return $this->redirectToRoute('file_manager', $fileManager->getQueryParameters());
        }
        $parameters['form'] = $form->createView();
        $parameters['formRename'] = $formRename->createView();

        return $this->render('@ArtgrisFileManager/manager.html.twig', $parameters);
    }

    /**
     * @Route("/rename/{fileName<.+>}", name="file_manager_rename")
     *
     * @param Request $request
     * @param $fileName
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Exception
     */
    public function renameFileAction(Request $request, $fileName)
    {
        $translator = $this->get('translator');
        $queryParameters = $request->query->all();
        $formRename = $this->createRenameForm();
        /* @var Form $formRename */
        $formRename->handleRequest($request);
        if ($formRename->isSubmitted() && $formRename->isValid()) {
            $data = $formRename->getData();
            $extension = $data['extension'] ? '.'.$data['extension'] : '';
            $newfileName = $data['name'].$extension;
            if ($newfileName !== $fileName && isset($data['name'])) {
                $success = false;
                $fileManager = $this->newFileManager($queryParameters);
                $newFilePath = sprintf("%s%s%s", $fileManager->getCurrentPath(), DIRECTORY_SEPARATOR, $newfileName);
                $newThumbPath = sprintf("%s%s%s%s%s",
                    $fileManager->getCurrentPath(),
                    DIRECTORY_SEPARATOR,
                    FileTypeService::THUMBNAIL_FOLDER_PREFIX,
                    DIRECTORY_SEPARATOR,
                    $newfileName
                );

                $oldFilePath = realpath(sprintf("%s%s%s", $fileManager->getCurrentPath(),DIRECTORY_SEPARATOR, $fileName));
                $oldThumbPath = realpath(sprintf("%s%s%s%s%s",
                        $fileManager->getCurrentPath(),
                        DIRECTORY_SEPARATOR,
                        FileTypeService::THUMBNAIL_FOLDER_PREFIX,
                        DIRECTORY_SEPARATOR,
                        $fileName)
                );
                if (0 !== strpos($newFilePath, $fileManager->getCurrentPath())) {
                    $this->addFlash('danger', $translator->trans('file.renamed.unauthorized'));
                } else {
                    $fs = new Filesystem();
                    $this->dispatch(
                        PreRenameEvent::NAME,
                        new PreRenameEvent(
                            $fileManager,
                            $fileName,
                            $newfileName,
                            $oldFilePath,
                            $newFilePath,
                            $oldThumbPath,
                            $newThumbPath
                        )
                    );
                    try {
                        $fs->rename($oldFilePath, $newFilePath);
                        if ($extension !== '') {
                            $fs->rename($oldThumbPath, $newThumbPath);
                        }
                        $this->addFlash('success', $translator->trans('file.renamed.success'));
                        $success = true;
                        //File has been renamed successfully
                    } catch (IOException $exception) {
                        $this->addFlash('danger', $translator->trans('file.renamed.danger'));
                    }
                }
                $this->dispatch(
                    PostRenameEvent::NAME,
                    new PostRenameEvent(
                        $fileManager,
                        $fileName,
                        $newfileName,
                        $oldFilePath,
                        $newFilePath,
                        $oldThumbPath,
                        $newThumbPath,
                        $success
                    )
                );
            } else {
                $this->addFlash('warning', $translator->trans('file.renamed.nochanged'));
            }

        }

        return $this->redirectToRoute('file_manager', $queryParameters);
    }

    /**
     * @Route("/upload/", name="file_manager_upload")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function uploadFileAction(Request $request)
    {
        $fileManager = $this->newFileManager($request->query->all());

        $options = [
            'upload_dir' => $fileManager->getCurrentPath().DIRECTORY_SEPARATOR,
            'upload_url' => $fileManager->getImagePath(),
            'accept_file_types' => $fileManager->getRegex(),
            'print_response' => false,
        ];
        if (isset($fileManager->getConfiguration()['upload'])) {
            $options += $fileManager->getConfiguration()['upload'];
        }

        $this->dispatch(PreUploadEvent::NAME, new PreUploadEvent($fileManager, $options));
        $uploadHandler = new UploadHandler($options);
        $response = $uploadHandler->response;

        foreach ($response['files'] as $file) {
            if (isset($file->error)) {
                $file->error = $this->get('translator')->trans($file->error);
            }

            if (isset($file->url) && !$fileManager->getImagePath()) {
                $file->url = $this->generateUrl('file_manager_file', array_merge($fileManager->getQueryParameters(), ['fileName' => $file->url]));
            }
        }

        $this->dispatch(PostUploadEvent::NAME, new PostUploadEvent($fileManager, $response));

        return new JsonResponse($response);
    }


    /**
     * @Route("/move/", name="file_manager_move")
     *
     * @param Request $request
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function moveFileAction(Request $request)
    {
        $queryParameters = $request->query->all();
        unset($queryParameters['json']);

        $this->move($this->newFileManager($queryParameters), $queryParameters['fileName'], $queryParameters['newPath']);

        return new Response();
    }

    /**
     * @Route("/moveFolder/", name="file_manager_move_folder")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function moveFolderAction(Request $request)
    {
        $queryParameters = $request->query->all();
        unset($queryParameters['json']);
        unset($queryParameters['route']);
        $originParameter = $queryParameters['origin'];
        $destinationParameter = $queryParameters['destination'];
        unset($queryParameters['origin']);
        unset($queryParameters['destination']);

        $fs = $this->newFileManager($queryParameters);

        $redirectTo = $this->generateUrl('file_manager', $fs->getQueryParameters());

        $destIsRoot = false;
        if($destinationParameter === '/') {
            $destIsRoot = true;
        }

        if($originParameter === '/') {
            return new Response($redirectTo);
        }

        //moving to inner folder, recursion
        if (strpos($destinationParameter, $originParameter ) === 0 && strpos(str_replace($originParameter, '', $destinationParameter), '/') !== false) {
            return new Response($redirectTo);
        }

        $expOrig = explode('/', $originParameter);

        $lastElem = array_pop($expOrig);

        if($destIsRoot && count($expOrig) === 1) {
            $expOrig[] = '';
        }

        //exit if we're moving to the same location we are
        if (implode('/', $expOrig) === $destinationParameter) {
            return new Response($redirectTo);
        }


        $filesystem = new Filesystem();

        $origin = sprintf(
            '%s%s',
            $fs->getBasePath(),
            urldecode($originParameter)
        );

        $destination = sprintf(
            '%s%s%s%s',
            $fs->getBasePath(),
            urldecode($destinationParameter),
            DIRECTORY_SEPARATOR,
            urldecode($lastElem)
        );
        $i = 0;
        while ($filesystem->exists($destination) && $i < self::ITERATION_LIMIT) {
            $destination = sprintf(
                '%s_copy',
                $destination
            );
            ++$i;
        }

        $this->dispatch(PreMoveFolderEvent::NAME, new PreMoveFolderEvent($fs, $origin, $destination));
        try {
            $filesystem->mirror($origin, $destination);
            $filesystem->remove($origin);
            $success = true;
        } catch(\Exception $e) {
            $success = false;
        }
        $this->dispatch(PostMoveFolderEvent::NAME, new PostMoveFolderEvent($fs, $origin, $destination, $success));
        return new Response($redirectTo);
    }

    /**
     * @Route("/file/{fileName}", name="file_manager_file")
     *
     * @param Request $request
     * @param $fileName
     *
     * @return BinaryFileResponse
     *
     * @throws \Exception
     */
    public function binaryFileResponseAction(Request $request, $fileName)
    {
        $fileManager = $this->newFileManager($request->query->all());

        return new BinaryFileResponse($fileManager->getCurrentPath().DIRECTORY_SEPARATOR.urldecode($fileName));
    }

    /**
     * @Route("/delete/", name="file_manager_delete", methods={"DELETE"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Exception
     */
    public function deleteAction(Request $request)
    {
        $form = $this->createDeleteForm();
        $form->handleRequest($request);
        $queryParameters = $request->query->all();
        if ($form->isSubmitted() && $form->isValid()) {
            // remove file
            $fileManager = $this->newFileManager($queryParameters);
            $fs = new Filesystem();
            if (isset($queryParameters['delete'])) {
                $is_delete = false;
                foreach ($queryParameters['delete'] as $fileName) {
                    $filePath = realpath(sprintf("%s%s%s",
                            $fileManager->getCurrentPath(),
                            DIRECTORY_SEPARATOR,
                            $fileName)
                    );
                    $thumbPath = realpath(
                        sprintf("%s%s%s%s%s",
                            $fileManager->getCurrentPath(),
                            DIRECTORY_SEPARATOR,
                            FileTypeService::THUMBNAIL_FOLDER_PREFIX,
                            DIRECTORY_SEPARATOR,
                            $fileName
                        )
                    );


                    if (0 !== strpos($filePath, $fileManager->getCurrentPath())) {
                        $this->addFlash('danger', 'file.deleted.danger');
                    } else {
                        $this->dispatch(PreDeleteFileEvent::NAME, new PreDeleteFileEvent($fileManager, $fileName, $filePath, $thumbPath));
                        try {
                            $fs->remove($filePath);
                            $fs->remove($thumbPath);
                            $is_delete = true;
                            $deleted = true;
                        } catch (IOException $exception) {
                            $this->addFlash('danger', 'file.deleted.unauthorized');
                            $deleted = false;
                        }
                        $this->dispatch(PostDeleteFileEvent::NAME, new PostDeleteFileEvent($fileManager, $fileName, $filePath, $thumbPath, $deleted));
                    }
                }
                if ($is_delete) {
                    $this->addFlash('success', 'file.deleted.success');
                }
                unset($queryParameters['delete']);
            } else {
                $pathToDelete = $fileManager->getCurrentPath();
                $this->dispatch(PreDeleteFolderEvent::NAME, new PreDeleteFolderEvent($fileManager, $pathToDelete));
                try {
                    $fs->remove($pathToDelete);
                    $this->addFlash('success', 'folder.deleted.success');
                    $deleted = true;
                } catch (IOException $exception) {
                    $this->addFlash('danger', 'folder.deleted.unauthorized');
                    $deleted = false;
                }

                $this->dispatch(PostDeleteFolderEvent::NAME, new PostDeleteFolderEvent($fileManager, $pathToDelete, $deleted));
                $queryParameters['route'] = dirname($fileManager->getCurrentRoute());
                if ($queryParameters['route'] = '/') {
                    unset($queryParameters['route']);
                }

                return $this->redirectToRoute('file_manager', $queryParameters);
            }
        }

        return $this->redirectToRoute('file_manager', $queryParameters);
    }

    /**
     * @return Form|\Symfony\Component\Form\FormInterface
     */
    private function createDeleteForm()
    {
        return $this->createFormBuilder()
            ->setMethod('DELETE')
            ->add('DELETE', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-danger',
                ],
                'label' => 'button.delete.action',
            ])
            ->getForm();
    }

    /**
     * @return mixed
     */
    private function createRenameForm()
    {
        return $this->createFormBuilder()
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Regex(['pattern' => '/^[A-Za-z0-9][A-Za-z0-9 -_]*[A-Za-z0-9]$/'])
                ],
                'attr' => [
                    'title' => $this->get('translator')->trans('randock.ypsa.medialibrary.name.pattern.restrictions'),
                    'pattern' => '^[A-Za-z0-9][A-Za-z0-9 -_]*[A-Za-z0-9]$'
                ],
                'label' => false,
            ])->add('extension', HiddenType::class)
            ->add('send', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
                'label' => 'button.rename.action',
            ])
            ->getForm();
    }

    /**
     * @param FileManager $fileManager
     * @param $path
     * @param string $parent
     * @param bool   $baseFolderName
     *
     * @return array|null
     */
    private function retrieveSubDirectories(FileManager $fileManager, $path, $parent = DIRECTORY_SEPARATOR, $baseFolderName = false)
    {
        $directories = new Finder();
        $directories->in($path)->ignoreUnreadableDirs()->exclude(FileTypeService::THUMBNAIL_FOLDER_PREFIX)->directories()->depth(0)->sortByType()->filter(function (SplFileInfo $file) {
            return $file->isReadable();
        });

        if ($baseFolderName) {
            $directories->name($baseFolderName);
        }
        $directoriesList = null;


        foreach ($directories as $directory) {
            /** @var SplFileInfo $directory */
            $fileName = $baseFolderName ? '' : $parent.$directory->getFilename();

            $queryParameters = $fileManager->getQueryParameters();
            $queryParameters['route'] = $fileName;
            $queryParametersRoute = $queryParameters;
            unset($queryParametersRoute['route']);
            $queryParametersParentRoute = $queryParameters;
            $queryParametersParentRoute['route'] = $parent;

            $filesNumber = $this->retrieveFilesNumber($directory->getPathname(), $fileManager->getRegex());
            $fileSpan = $filesNumber > 0 ? " <span class='label label-default'>{$filesNumber}</span>" : '';

            $directoriesList[] = [
                'text' => $directory->getFilename().$fileSpan,
                'icon' => 'fa fa-folder-o',
                'children' => $this->retrieveSubDirectories($fileManager, $directory->getPathname(), $fileName.DIRECTORY_SEPARATOR),
                'li_attr' => [
                    'class' => 'dir',
                    'data-name' => trim($directory->getFilename(), '/'),
                    'data-path' => $fileName,
                    'data-href' => $fileName ? $this->generateUrl('file_manager', $queryParameters) : $this->generateUrl('file_manager', $queryParametersRoute),
                    'data-deletehref' => $fileName ? $this->generateUrl('file_manager_delete', $queryParametersRoute + ['delete[]' => trim($fileName, '/')]) : $this->generateUrl('file_manager', $queryParametersRoute),
                    'data-renamehref' => $fileName ? $this->generateUrl('file_manager_rename', $queryParametersParentRoute + ['fileName' => trim($directory->getFilename(), '/')]) : $this->generateUrl('file_manager', $queryParametersRoute),
                ],
                'a_attr' => [
                    'href' => $fileName ? $this->generateUrl('file_manager', $queryParameters) : $this->generateUrl('file_manager', $queryParametersRoute),
                ], 'state' => [
                    'selected' => $fileManager->getCurrentRoute() === $fileName,
                    'opened' => true,
                ],
            ];
        }

        return $directoriesList;
    }

    /**
     * Tree Iterator.
     *
     * @param $path
     * @param $regex
     *
     * @return int
     */
    private function retrieveFilesNumber($path, $regex)
    {
        $files = new Finder();
        $files->in($path)->files()->depth(0)->name($regex);

        return iterator_count($files);
    }

    /*
     * Base Path
     */
    private function getBasePath($queryParameters)
    {
        $conf = $queryParameters['conf'];
        $managerConf = $this->container->getParameter('artgris_file_manager')['conf'];
        if (isset($managerConf[$conf]['dir'])) {
            return $managerConf[$conf];
        }

        if (isset($managerConf[$conf]['service'])) {
            $extra = isset($queryParameters['extra']) ? $queryParameters['extra'] : [];
            $conf = $this->get($managerConf[$conf]['service'])->getConf($extra);

            return $conf;
        }

        throw new \RuntimeException('Please define a "dir" or a "service" parameter in your config.yml');
    }

    /**
     * @return mixed
     */
    private function getKernelRoute()
    {
        return $this->container->getParameter('kernel.root_dir');
    }

    /**
     * @param $queryParameters
     *
     * @return FileManager
     *
     * @throws \Exception
     */
    private function newFileManager($queryParameters)
    {
        if (!isset($queryParameters['conf'])) {
            throw new \RuntimeException('Please define a conf parameter in your route');
        }
        $webDir = $this->container->getParameter('artgris_file_manager')['web_dir'];

        $this->fileManager = new FileManager($queryParameters, $this->getBasePath($queryParameters), $this->getKernelRoute(), $this->get('router'), $webDir);

        return $this->fileManager;
    }

    protected function dispatch(string $eventName, Event $event)
    {
        $this->get('event_dispatcher')->dispatch($eventName, $event);
    }


    /**
     * @param FileManager $fileManager
     * @param string $fileName
     * @param string $newPath
     */
    private function move(FileManager $fileManager, $fileName, $newPath)
    {

        $filesystem = new Filesystem();

        $newFilePath = sprintf('%s%s%s%s',
            $fileManager->getBasePath(),
            urldecode($newPath),
            \DIRECTORY_SEPARATOR,
            $fileName
        );

        $oldFilePath = realpath(
            sprintf('%s%s%s',
                $fileManager->getCurrentPath(),
                \DIRECTORY_SEPARATOR,
                $fileName
            )
        );

        $newThumbPathFolder = sprintf('%s%s%s%s',
            $fileManager->getBasePath(),
            urldecode($newPath),
            \DIRECTORY_SEPARATOR,
            FileTypeService::THUMBNAIL_FOLDER_PREFIX);

        $newThumbPath = sprintf('%s%s%s%s%s%s',
            $fileManager->getBasePath(),
            urldecode($newPath),
            \DIRECTORY_SEPARATOR,
            FileTypeService::THUMBNAIL_FOLDER_PREFIX,
            \DIRECTORY_SEPARATOR,
            $fileName);



        $oldThumbPath = realpath(
            sprintf('%s%s%s%s%s',
                $fileManager->getCurrentPath(),
                \DIRECTORY_SEPARATOR,
                FileTypeService::THUMBNAIL_FOLDER_PREFIX,
                \DIRECTORY_SEPARATOR,
                $fileName
            )
        );


        if ($newFilePath === $oldFilePath) {
            return;
        }

        if (0 === strpos($newFilePath, $fileManager->getBasePath())) {
            $this->dispatch(
                PreMoveFileEvent::NAME,
                new PreMoveFileEvent($fileManager, $fileName, $newFilePath, $oldFilePath, $newThumbPath, $oldThumbPath)
            );
            try {
                $filesystem->rename($oldFilePath, $newFilePath);

                if (!$filesystem->exists($newThumbPathFolder)) {
                    $filesystem->mkdir($newThumbPathFolder);
                }
                if($filesystem->exists($oldThumbPath)){

                    $filesystem->rename($oldThumbPath, $newThumbPath);
                }
                $success = true;
            } catch (IOException $exception) {
                $success = false;
            }
            $this->dispatch(
                PostMoveFileEvent::NAME,
                new PostMoveFileEvent(
                    $fileManager,
                    $fileName,
                    $newFilePath,
                    $oldFilePath,
                    $newThumbPath,
                    $oldThumbPath,
                    $success
                )
            );
        }
    }


}
