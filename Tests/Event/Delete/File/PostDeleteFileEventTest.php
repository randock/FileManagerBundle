<?php

namespace Artgris\Bundle\FileManagerBundle\Tests\Event\Delete\File;

use Artgris\Bundle\FileManagerBundle\Event\Delete\File\PostDeleteFileEvent;
use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;

class PostDeleteFileEventTest extends \PHPUnit_Framework_TestCase
{

    public function testGetters()
    {
        $fileManager = $this->createMock(FileManager::class);
        $filePath = 'path/to/file';
        $thumbPath = 'path/to/thumb';
        $success = true;
        $postDeleteFileEvent = new PostDeleteFileEvent($fileManager, $filePath, $thumbPath, $success);

        $this->assertSame($fileManager, $postDeleteFileEvent->getFileManager());
        $this->assertSame($filePath, $postDeleteFileEvent->getFilePath());
        $this->assertSame($thumbPath, $postDeleteFileEvent->getThumbPath());
        $this->assertSame($success, $postDeleteFileEvent->isSuccess());

    }
}
