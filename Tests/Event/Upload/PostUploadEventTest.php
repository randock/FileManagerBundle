<?php

namespace Artgris\Bundle\FileManagerBundle\Tests\Event\Upload;

use Artgris\Bundle\FileManagerBundle\Event\Upload\PostUploadEvent;
use Artgris\Bundle\FileManagerBundle\Event\Upload\ValueObject\UploadedFile;
use Artgris\Bundle\FileManagerBundle\Helpers\FileManager;
use Artgris\Bundle\FileManagerBundle\Tests\Event\Upload\ValueObject\UploadedFileTest;

class PostUploadEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileManager
     */
    private $fileManager;

    protected function setUp()
    {
        $this->fileManager = $this->createMock(FileManager::class);
    }

    public function testGettersWithNoFiles()
    {
        $response = ['property' => 'value'];
        $event = new PostUploadEvent($this->fileManager, $response);
        $this->assertSame($this->fileManager, $event->getFileManager());
        $this->assertSame($response, $event->getResponse());
        $this->assertSame([], $event->getFiles());
    }


    public function testGetFiles()
    {
        $response = ['property' => 'value', 'files' => $this->filesFixture()];
        $event = new PostUploadEvent($this->fileManager, $response);
        $this->assertSame($this->fileManager, $event->getFileManager());
        $this->assertSame($response, $event->getResponse());
        $this->assertCount(3, $event->getFiles());
        foreach ($event->getFiles() as $file) {
            $this->assertInstanceOf(UploadedFile::class, $file);
        }
    }

    private function filesFixture(): array
    {
        $files = [];
        for ($i = 0; $i < 3; ++$i) {
            $file = [
                'name' => UploadedFileTest::NAME,
                'size' => UploadedFileTest::SIZE,
                'type' => UploadedFileTest::TYPE,
                'url' => UploadedFileTest::URL,
                'error' => null
            ];
            $files[] = (object) $file;
        }
        return $files;
    }
}
