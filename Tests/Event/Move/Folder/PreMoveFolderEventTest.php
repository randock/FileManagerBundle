<?php

namespace Artgris\Bundle\FileManagerBundle\Tests\Event\Move\Folder;

use Artgris\Bundle\FileManagerBundle\Event\Move\Folder\PreMoveFolderEvent;
use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;

class PreMoveFolderEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $fileManager = $this->createMock(FileManager::class);
        $origin = 'path/origin';
        $destination = 'path/destination';

        $preMoveFolderEvent = new PreMoveFolderEvent(
            $fileManager,
            $origin,
            $destination
        );

        $this->assertSame($fileManager, $preMoveFolderEvent->getFileManager());
        $this->assertSame($origin, $preMoveFolderEvent->getOrigin());
        $this->assertSame($destination, $preMoveFolderEvent->getDestination());

    }
}
