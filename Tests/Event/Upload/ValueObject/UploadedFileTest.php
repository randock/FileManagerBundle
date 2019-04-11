<?php

namespace Artgris\Bundle\FileManagerBundle\Tests\Event\Upload\ValueObject;

use Artgris\Bundle\FileManagerBundle\Event\Upload\ValueObject\UploadedFile;

class UploadedFileTest extends \PHPUnit_Framework_TestCase
{
    public const NAME = 'myFile.txt';
    public const SIZE = '4096';
    public const TYPE = 'text/plain';
    public const URL = 'https//url';


    public function testGetters()
    {
        $file = self::newUploadFile();

        $this->assertSame(self::NAME, $file->getName());
        $this->assertSame(self::SIZE, $file->getSize());
        $this->assertSame(self::TYPE, $file->getType());
        $this->assertSame(self::URL, $file->getUrl());
        $this->assertNull($file->getError());
    }

    /**
     * @param string|null $name
     * @param string|null $size
     * @param string|null $type
     * @param string|null $url
     * @param string|null $error
     *
     * @return UploadedFile
     */
    public static function newUploadFile(
        ?string $name = null,
        ?string $size = null,
        ?string $type = null,
        ?string $url = null,
        ?string $error = null
    ): UploadedFile {

        $genericFile = [
            'name' => $name ?? self::NAME,
            'size' => $size ?? self::SIZE,
            'type' => $type ?? self::TYPE,
            'url' => $url ?? self::URL,
            'error' => $error
        ];
        return new UploadedFile((object) $genericFile);
    }

}
