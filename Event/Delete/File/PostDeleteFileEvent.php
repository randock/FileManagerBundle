<?php

declare(strict_types=1);

namespace Artgris\Bundle\FileManagerBundle\Event\Delete\File;

use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;
use Symfony\Component\EventDispatcher\Event;

class PostDeleteFileEvent extends Event
{
    public const NAME = 'file_manager.post_delete_file';

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
     * @var bool
     */
    private $success;

    /**
     * PostDeleteFileEvent constructor.
     *
     * @param FileManager $fileManager
     * @param string      $filePath
     * @param string      $thumbPath
     * @param bool        $success
     */
    public function __construct(FileManager $fileManager, string $filePath, string $thumbPath, bool $success)
    {
        $this->fileManager = $fileManager;
        $this->filePath = $filePath;
        $this->thumbPath = $thumbPath;
        $this->success = $success;
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

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }
}