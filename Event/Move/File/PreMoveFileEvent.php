<?php

declare(strict_types=1);

namespace Artgris\Bundle\FileManagerBundle\Event\Move\File;

use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;
use Symfony\Component\EventDispatcher\Event;

class PreMoveFileEvent extends Event
{
    public const NAME = 'file_manager.pre_move_file';

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
    private $newFilePath;

    /**
     * @var string
     */
    private $oldFilePath;

    /**
     * @var string
     */
    private $newThumbPath;

    /**
     * @var string
     */
    private $oldThumbPath;

    /**
     * PreMoveFileEvent constructor.
     *
     * @param FileManager $fileManager
     * @param string      $filename
     * @param string      $newFilePath
     * @param string      $oldFilePath
     * @param string      $newThumbPath
     * @param string      $oldThumbPath
     */
    public function __construct(
        FileManager $fileManager,
        string $filename,
        string $newFilePath,
        string $oldFilePath,
        string $newThumbPath,
        string $oldThumbPath
    ) {
        $this->fileManager = $fileManager;
        $this->filename = $filename;
        $this->newFilePath = $newFilePath;
        $this->oldFilePath = $oldFilePath;
        $this->newThumbPath = $newThumbPath;
        $this->oldThumbPath = $oldThumbPath;
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
    public function getNewFilePath(): string
    {
        return $this->newFilePath;
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
    public function getNewThumbPath(): string
    {
        return $this->newThumbPath;
    }

    /**
     * @return string
     */
    public function getOldThumbPath(): string
    {
        return $this->oldThumbPath;
    }

}