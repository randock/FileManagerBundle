<?php

declare(strict_types=1);

namespace Artgris\Bundle\FileManagerBundle\Event\Rename;

use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;
use Symfony\Component\EventDispatcher\Event;

class PostRenameEvent extends Event
{
    public const NAME = 'file_manager.post_rename';

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $newFilename;

    /**
     * @var string
     */
    private $oldFilePath;

    /**
     * @var string
     */
    private $newFilePath;

    /**
     * @var string
     */
    private $oldThumbPath;

    /**
     * @var string
     */
    private $newThumbPath;

    /**
     * @var bool
     */
    private $success;

    /**
     * PostRenameEvent constructor.
     *
     * @param FileManager $fileManager
     * @param string      $filename
     * @param string      $newFilename
     * @param string      $oldFilePath
     * @param string      $newFilePath
     * @param string      $oldThumbPath
     * @param string      $newThumbPath
     * @param bool        $success
     */
    public function __construct(
        FileManager $fileManager,
        string $filename,
        string $newFilename,
        string $oldFilePath,
        string $newFilePath,
        string $oldThumbPath,
        string $newThumbPath,
        bool $success
    ) {
        $this->fileManager = $fileManager;
        $this->filename = $filename;
        $this->newFilename = $newFilename;
        $this->oldFilePath = $oldFilePath;
        $this->newFilePath = $newFilePath;
        $this->oldThumbPath = $oldThumbPath;
        $this->newThumbPath = $newThumbPath;
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
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getNewFilename(): string
    {
        return $this->newFilename;
    }

    /**
     * @return string
     */
    public function getOldFilePath(): string
    {
        return $this->oldFilePath;
    }

    /**
     * @return string
     */
    public function getNewFilePath(): string
    {
        return $this->newFilePath;
    }

    /**
     * @return string
     */
    public function getOldThumbPath(): string
    {
        return $this->oldThumbPath;
    }

    /**
     * @return string
     */
    public function getNewThumbPath(): string
    {
        return $this->newThumbPath;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

}