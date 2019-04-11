<?php

namespace Artgris\Bundle\FileManagerBundle\Tests\Event\Move\File;

use Artgris\Bundle\FileManagerBundle\Event\Move\File\PreMoveFileEvent;
use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;

class PreMoveFileEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $fileManager = $this->createMock(FileManager::class);
        $filename = 'myFile.txt';
        $newFilePath = 'new/file/path';
        $oldFilePath = 'old/file/path';
        $newThumbsPath = 'new/thumbs/path';
        $oldThumbsPath = 'old/thumbs/path';

        $preMoveFileEvent = new PreMoveFileEvent(
            $fileManager,
            $filename,
            $newFilePath,
            $oldFilePath,
            $newThumbsPath,
            $oldThumbsPath
        );

        $this->assertSame($fileManager, $preMoveFileEvent->getFileManager());
        $this->assertSame($filename, $preMoveFileEvent->getFilename());
        $this->assertSame($newFilePath, $preMoveFileEvent->getNewFilePath());
        $this->assertSame($oldFilePath, $preMoveFileEvent->getOldFilePath());
        $this->assertSame($newThumbsPath, $preMoveFileEvent->getNewThumbPath());
        $this->assertSame($oldThumbsPath, $preMoveFileEvent->getOldThumbPath());
    }
}
