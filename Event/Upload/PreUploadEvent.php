<?php

declare(strict_types=1);

namespace Artgris\Bundle\FileManagerBundle\Event\Upload;

use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;
use Symfony\Component\EventDispatcher\Event;

class PreUploadEvent extends Event
{
    public const NAME = 'file_manager.pre_update';

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var array
     */
    private $options;

    /**
     * PreUploadEvent constructor.
     *
     * @param FileManager $fileManager
     * @param array       $options
     */
    public function __construct(FileManager $fileManager, array $options)
    {
        $this->options = $options;
        $this->fileManager = $fileManager;
    }

    /**
     * @param string $property
     *
     * @return mixed
     */
    public function getOption(string $property)
    {
        if (array_key_exists($property, $this->options)) {
            return $this->options[$property];
        }
        return false;
    }

    /**
     * @return FileManager
     */
    public function getFileManager(): FileManager
    {
        return $this->fileManager;
    }
}