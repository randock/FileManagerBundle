<?php

declare(strict_types=1);

namespace Artgris\Bundle\FileManagerBundle\Event\Update;

use Artgris\Bundle\FileManagerBundle\Event\Update\ValueObject\UploadedFile;
use Symfony\Component\EventDispatcher\Event;

class PostUpdateEvent extends Event
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
     * PostUpdateEvent constructor.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
        if (isset($response['files'])) {

            foreach ($response['files'] as $file) {
                $this->files = new UploadedFile($file);
            }
        }
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