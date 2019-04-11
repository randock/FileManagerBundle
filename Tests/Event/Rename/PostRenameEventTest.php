<?php

namespace Artgris\Bundle\FileManagerBundle\Tests\Event\Rename;

use Artgris\Bundle\FileManagerBundle\Event\Rename\PostRenameEvent;
use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;

class PostRenameEventTest extends \PHPUnit_Framework_TestCase
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
        $success = false;

        $postRenameEvent = new PostRenameEvent(
            $fileManager,
            $filename,
            $newFilename,
            $oldFilePath,
            $newFilePath,
            $oldThumbsPath,
            $newThumbsPath,
            $success
        );

        $this->assertSame($fileManager, $postRenameEvent->getFileManager());
        $this->assertSame($filename, $postRenameEvent->getFilename());
        $this->assertSame($newFilename, $postRenameEvent->getNewFilename());
        $this->assertSame($newFilePath, $postRenameEvent->getNewFilePath());
        $this->assertSame($oldFilePath, $postRenameEvent->getOldFilePath());
        $this->assertSame($newThumbsPath, $postRenameEvent->getNewThumbPath());
        $this->assertSame($oldThumbsPath, $postRenameEvent->getOldThumbPath());
        $this->assertSame($success, $postRenameEvent->isSuccess());
    }
}
