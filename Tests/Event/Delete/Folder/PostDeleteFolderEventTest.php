<?php

namespace Artgris\Bundle\FileManagerBundle\Tests\Event\Delete\Folder;

use Artgris\Bundle\FileManagerBundle\Event\Delete\Folder\PostDeleteFolderEvent;
use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;

class PostDeleteFolderEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $fileManager = $this->createMock(FileManager::class);
        $pathToDelete = 'path/to/delete';
        $success = false;
        $postDeleteFolderEvent = new PostDeleteFolderEvent($fileManager, $pathToDelete, $success);
        $this->assertSame($fileManager, $postDeleteFolderEvent->getFileManager());
        $this->assertSame($pathToDelete, $postDeleteFolderEvent->getPathToDelete());
        $this->assertSame($success, $postDeleteFolderEvent->isSuccess());
    }
}
