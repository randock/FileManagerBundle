<?php

namespace Artgris\Bundle\FileManagerBundle\Tests\Event\Upload;

use Artgris\Bundle\FileManagerBundle\Event\Upload\PreUploadEvent;
use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;

class PreUploadEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileManager
     */
    private $fileManager;

    protected function setUp()
    {
        $this->fileManager = $this->createMock(FileManager::class);
    }


    public function testGetFileManager()
    {
        $event = new PreUploadEvent($this->fileManager, []);
        $this->assertSame($this->fileManager, $event->getFileManager());
    }


    public function testGetOption()
    {
        $event = new PreUploadEvent($this->fileManager, [
            'property'=> 'value'
        ]);

        $this->assertFalse($event->getOption('nonProperty'));
        $this->assertSame('value', $event->getOption('property'));
    }
}
