<?php

declare(strict_types=1);

namespace Artgris\Bundle\FileManagerBundle\Event\Move\Folder;

use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;
use Symfony\Component\EventDispatcher\Event;

class PreMoveFolderEvent extends Event
{
    public const NAME = 'file_manager.pre_rename';

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var string
     */
    private $origin;

    /**
     * @var string
     */
    private $destination;

    /**
     * PreMoveFolderEvent constructor.
     *
     * @param FileManager $fileManager
     * @param string      $origin
     * @param string      $destination
     */
    public function __construct(FileManager $fileManager, string $origin, string $destination)
    {
        $this->fileManager = $fileManager;
        $this->origin = $origin;
        $this->destination = $destination;
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
    public function getOrigin(): string
    {
        return $this->origin;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }


}