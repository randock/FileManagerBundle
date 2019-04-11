<?php

namespace Artgris\Bundle\FileManagerBundle\Tests\Event\Delete\Folder;

use Artgris\Bundle\FileManagerBundle\Event\Delete\Folder\PreDeleteFolderEvent;
use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;

class PreDeleteFolderEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $fileManager = $this->createMock(FileManager::class);
        $pathToDelete = 'path/to/delete';
        $preDeleteFolderEvent = new PreDeleteFolderEvent($fileManager, $pathToDelete);
        $this->assertSame($fileManager, $preDeleteFolderEvent->getFileManager());
        $this->assertSame($pathToDelete, $preDeleteFolderEvent->getPathToDelete());
    }
}
