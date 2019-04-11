<?php

declare(strict_types=1);

namespace Artgris\Bundle\FileManagerBundle\Event\Delete\Folder;

use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;
use Symfony\Component\EventDispatcher\Event;

class PreDeleteFolderEvent extends Event
{
    public const NAME = 'file_manager.pre_delete_folder';

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var string
     */
    private $pathToDelete;


    /**
     * PreDeleteFolderEvent constructor.
     *
     * @param FileManager $fileManager
     * @param string      $pathToDelete
     */
    public function __construct(FileManager $fileManager, string $pathToDelete)
    {
        $this->fileManager = $fileManager;
        $this->pathToDelete = $pathToDelete;
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