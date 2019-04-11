<?php

declare(strict_types=1);

namespace Artgris\Bundle\FileManagerBundle\Event\Upload;

use Artgris\Bundle\FileManagerBundle\Event\Upload\ValueObject\UploadedFile;
use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;
use Symfony\Component\EventDispatcher\Event;

class PostUploadEvent extends Event
{
    public const NAME = 'file_manager.post_update';

    /**
     * @var array|UploadedFile[]
     */
    private $files = [];

    /**
     * @var array
     */
    private $response;

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * PostUploadEvent constructor.
     *
     * @param FileManager $fileManager
     * @param array       $response
     */
    public function __construct(FileManager $fileManager, array $response)
    {
        $this->response = $response;
        if (isset($response['files'])) {

            foreach ($response['files'] as $file) {
                $this->files[] = new UploadedFile($file);
            }
        }
        $this->fileManager = $fileManager;
    }

    /**
     * @return FileManager
     */
    public function getFileManager(): FileManager
    {
        return $this->fileManager;
    }

    /**
     * @return array|UploadedFile[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }


}