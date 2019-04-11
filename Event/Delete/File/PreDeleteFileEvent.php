<?php

declare(strict_types=1);

namespace Artgris\Bundle\FileManagerBundle\Event\Delete\File;

use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;
use Symfony\Component\EventDispatcher\Event;

class PreDeleteFileEvent extends Event
{

    public const NAME = 'file_manager.pre_delete_file';

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var string
     */
    private $thumbPath;

    /**
     * PostDeleteFileEvent constructor.
     *
     * @param FileManager $fileManager
     * @param string      $filePath
     * @param string      $thumbPath
     */
    public function __construct(FileManager $fileManager, string $filePath, string $thumbPath)
    {
        $this->fileManager = $fileManager;
        $this->filePath = $filePath;
        $this->thumbPath = $thumbPath;
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
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getThumbPath(): string
    {
        return $this->thumbPath;
    }

}