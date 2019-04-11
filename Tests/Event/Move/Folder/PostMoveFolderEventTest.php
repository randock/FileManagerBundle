<?php

namespace Artgris\Bundle\FileManagerBundle\Tests\Event\Move\Folder;

use Artgris\Bundle\FileManagerBundle\Event\Move\Folder\PostMoveFolderEvent;
use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;

class PostMoveFolderEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $fileManager = $this->createMock(FileManager::class);
        $origin = 'path/origin';
        $destination = 'path/destination';
        $success = true;

        $postMoveFolderEvent = new PostMoveFolderEvent(
            $fileManager,
            $origin,
            $destination,
            $success
        );

        $this->assertSame($fileManager, $postMoveFolderEvent->getFileManager());
        $this->assertSame($origin, $postMoveFolderEvent->getOrigin());
        $this->assertSame($destination, $postMoveFolderEvent->getDestination());
        $this->assertSame($success, $postMoveFolderEvent->isSuccess());
    }
}
