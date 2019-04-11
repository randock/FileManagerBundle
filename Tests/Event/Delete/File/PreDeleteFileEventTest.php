<?php

namespace Artgris\Bundle\FileManagerBundle\Tests\Event\Delete\File;

use Artgris\Bundle\FileManagerBundle\Event\Delete\File\PreDeleteFileEvent;
use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;

class PreDeleteFileEventTest extends \PHPUnit_Framework_TestCase
{

    public function testGetters()
    {
        $fileManager = $this->createMock(FileManager::class);
        $filename = 'myFile.txt';
        $filePath = 'path/to/file';
        $thumbPath = 'path/to/thumb';
        $preDeleteFileEvent = new PreDeleteFileEvent($fileManager, $filename, $filePath, $thumbPath);

        $this->assertSame($fileManager, $preDeleteFileEvent->getFileManager());
        $this->assertSame($filename, $preDeleteFileEvent->getFilename());
        $this->assertSame($filePath, $preDeleteFileEvent->getFilePath());
        $this->assertSame($thumbPath, $preDeleteFileEvent->getThumbPath());
    }
}
