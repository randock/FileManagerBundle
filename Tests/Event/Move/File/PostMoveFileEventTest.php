<?php

namespace Artgris\Bundle\FileManagerBundle\Tests\Event\Move\File;

use Artgris\Bundle\FileManagerBundle\Event\Move\File\PostMoveFileEvent;
use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;

class PostMoveFileEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $fileManager = $this->createMock(FileManager::class);
        $filename = 'myFile.txt';
        $newFilePath = 'new/file/path';
        $oldFilePath = 'old/file/path';
        $newThumbsPath = 'new/thumbs/path';
        $oldThumbsPath = 'old/thumbs/path';
        $success = false;

        $postMoveFileEvent = new PostMoveFileEvent(
            $fileManager,
            $filename,
            $newFilePath,
            $oldFilePath,
            $newThumbsPath,
            $oldThumbsPath,
            $success
        );

        $this->assertSame($fileManager, $postMoveFileEvent->getFileManager());
        $this->assertSame($filename, $postMoveFileEvent->getFilename());
        $this->assertSame($newFilePath, $postMoveFileEvent->getNewFilePath());
        $this->assertSame($oldFilePath, $postMoveFileEvent->getOldFilePath());
        $this->assertSame($newThumbsPath, $postMoveFileEvent->getNewThumbPath());
        $this->assertSame($oldThumbsPath, $postMoveFileEvent->getOldThumbPath());
        $this->assertSame($success, $postMoveFileEvent->isSuccess());
    }
}
