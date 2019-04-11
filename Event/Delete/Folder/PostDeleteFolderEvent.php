<?php

declare(strict_types=1);

namespace Artgris\Bundle\FileManagerBundle\Event\Delete\Folder;

use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;
use Symfony\Component\EventDispatcher\Event;

class PostDeleteFolderEvent extends Event
{
    public const NAME = 'file_manager.post_delete_folder';

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var string
     */
    private $pathToDelete;

    /**
     * @var bool
     */
    private $success;


    /**
     * PreDeleteFolderEvent constructor.
     *
     * @param FileManager $fileManager
     * @param string      $pathToDelete
     * @param bool        $success
     */
    public function __construct(FileManager $fileManager, string $pathToDelete, bool $success)
    {
        $this->fileManager = $fileManager;
        $this->pathToDelete = $pathToDelete;
        $this->success = $success;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return FileManager
     */
    public function getFileManager(): FileManager
    {
        return $this->fileManager;
    }

    /**
     * @return string
     */
    public function getPathToDelete(): string
    {
        return $this->pathToDelete;
    }



}