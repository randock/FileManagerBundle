<?php

declare(strict_types=1);

namespace Artgris\Bundle\FileManagerBundle\Event\Upload\ValueObject;

class UploadedFile
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $size;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string|null
     */
    private $error;

    /**
     * UploadedFile constructor.
     *
     * @param \stdClass $file
     */
    public function __construct(\stdClass $file)
    {
        $this->name = $file->name;
        $this->size = $file->size;
        $this->type = $file->type;
        $this->url = $file->url ?? '';
        $this->error = $file->error ?? null;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }
}