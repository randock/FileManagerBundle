<?php

namespace Artgris\Bundle\FileManagerBundle\Tests\Event\Rename;

use Artgris\Bundle\FileManagerBundle\Event\Rename\PreRenameEvent;
use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;

class PreRenameEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $fileManager = $this->createMock(FileManager::class);
        $filename = 'myFile.txt';
        $newFilename = 'myNewFile.txt';
        $newFilePath = 'new/file/path';
        $oldFilePath = 'old/file/path';
        $newThumbsPath = 'new/thumbs/path';
        $oldThumbsPath = 'old/thumbs/path';

        $preRenameEvent = new PreRenameEvent(
            $fileManager,
            $filename,
            $newFilename,
            $oldFilePath,
            $newFilePath,
            $oldThumbsPath,
            $newThumbsPath
        );

        $this->assertSame($fileManager, $preRenameEvent->getFileManager());
        $this->assertSame($filename, $preRenameEvent->getFilename());
        $this->assertSame($newFilename, $preRenameEvent->getNewFilename());
        $this->assertSame($newFilePath, $preRenameEvent->getNewFilePath());
        $this->assertSame($oldFilePath, $preRenameEvent->getOldFilePath());
        $this->assertSame($newThumbsPath, $preRenameEvent->getNewThumbPath());
        $this->assertSame($oldThumbsPath, $preRenameEvent->getOldThumbPath());
    }
}
