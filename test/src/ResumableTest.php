<?php
namespace Dilab\Test;

use Dilab\Network\SimpleRequest;
use Dilab\Resumable;
use Cake\Filesystem\File;

/**
 * Class ResumbableTest
 * @package Dilab\Test
 * @property $resumbable Resumable
 * @property $request Request
 * @property $response Response
 */
class ResumbableTest extends \PHPUnit_Framework_TestCase
{
    public $resumbable;

    protected $provider;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder('Dilab\Network\SimpleRequest')
                        ->getMock();

        $this->response = $this->getMockBuilder('Dilab\Network\SimpleResponse')
                        ->getMock();
    }

    public function tearDown()
    {
        unset($this->request);
        unset($this->response);
        parent::tearDown();
    }

    public function testProcessHandleChunk()
    {
        $resumableParams = array(
            'resumableChunkNumber'=> 3,
            'resumableTotalChunks'=> 600,
            'resumableChunkSize'=>  200,
            'resumableIdentifier'=> 'identifier',
            'resumableFilename'=> 'mock.png',
            'resumableRelativePath'=> 'upload',
        );

        $this->request->method('is')->will($this->returnValue(true));

        $this->request->method('file')
                    ->will($this->returnValue(array(
                            'name'=> 'mock.png',
                            'tmp_name'=>  'test/files/mock.png.part3',
                            'error'=> 0,
                            'size'=> 27000,
                        )));

        $this->request->method('data')->willReturn($resumableParams);

        $this->resumbable = $this->getMockBuilder('Dilab\Resumable')
                                ->setConstructorArgs(array($this->request,$this->response))
                                ->setMethods(array('handleChunk'))
                                ->getMock();

        $this->resumbable->expects($this->once())
                        ->method('handleChunk')
                        ->willReturn(true);

        $this->resumbable->process();
    }

    public function testProcessHandleTestChunk()
    {
        $resumableParams = array(
            'resumableChunkNumber'=> 3,
            'resumableTotalChunks'=> 600,
            'resumableChunkSize'=>  200,
            'resumableIdentifier'=> 'identifier',
            'resumableFilename'=> 'mock.png',
            'resumableRelativePath'=> 'upload',
        );

        $this->request->method('is')->will($this->returnValue(true));

        $this->request->method('file')->will($this->returnValue(array()));

        $this->request->method('data')->willReturn($resumableParams);

        $this->resumbable = $this->getMockBuilder('Dilab\Resumable')
                                ->setConstructorArgs(array($this->request,$this->response))
                                ->setMethods(array('handleTestChunk'))
                                ->getMock();

        $this->resumbable->expects($this->once())
                        ->method('handleTestChunk')
                        ->willReturn(true);

        $this->resumbable->process();
    }

    public function testHandleTestChunk()
    {
        $this->request->method('is')
                      ->will($this->returnValue(true));

        $this->request->method('data')
                      ->willReturn(array(
                           'resumableChunkNumber'=> 1,
                           'resumableTotalChunks'=> 600,
                           'resumableChunkSize'=>  200,
                           'resumableIdentifier'=> 'identifier',
                           'resumableFilename'=> 'mock.png',
                           'resumableRelativePath'=> 'upload',
                      ));

        $this->response->expects($this->once())
                        ->method('header')
                        ->with($this->equalTo(200));

        $this->resumbable = new Resumable($this->request,$this->response);
        $this->resumbable->tempFolder = 'test/tmp';
        $this->resumbable->handleTestChunk();
    }

    public function testHandleChunk() {
        $resumableParams = array(
            'resumableChunkNumber'=> 3,
            'resumableTotalChunks'=> 600,
            'resumableChunkSize'=>  200,
            'resumableIdentifier'=> 'identifier',
            'resumableFilename'=> 'mock.png',
            'resumableRelativePath'=> 'upload',
        );


        $this->request->method('is')
            ->will($this->returnValue(true));

        $this->request->method('data')
                ->willReturn($resumableParams);

        $this->request->method('file')
                ->willReturn(array(
                    'name'=> 'mock.png',
                    'tmp_name'=>  'test/files/mock.png.part3',
                    'error'=> 0,
                    'size'=> 27000,
                ));

        $this->resumbable = new Resumable($this->request, $this->response);
        $this->resumbable->tempFolder = 'test/tmp';
        $this->resumbable->uploadFolder = 'test/uploads';
        $this->resumbable->deleteTmpFolder = false;
        $this->resumbable->handleChunk();

        $this->assertFileExists('test/uploads/mock.png');
        unlink('test/tmp/identifier/mock.png.part3');
        unlink('test/uploads/mock.png');
    }

    public function testResumableParamsGetRequest()
    {
        $resumableParams = array(
            'resumableChunkNumber'=> 1,
            'resumableTotalChunks'=> 100,
            'resumableChunkSize'=>  1000,
            'resumableIdentifier'=> 100,
            'resumableFilename'=> 'mock_file_name',
            'resumableRelativePath'=> 'upload',
        );

        $this->request = $this->getMockBuilder('Dilab\Network\SimpleRequest')
            ->getMock();

        $this->request->method('is')
            ->will($this->returnValue(true));

        $this->request->method('data')->willReturn($resumableParams);

        $this->resumbable = new Resumable($this->request,$this->response);
        $this->assertEquals($resumableParams, $this->resumbable->resumableParams());
    }

    public function isFileUploadCompleteProvider()
    {
        return array(
            array('mock.png', 'files', 20, 60, true),
            array('mock.png','files', 25, 60, true),
            array('mock.png','files', 10, 60, false),
        );
    }

    /**
     *
     * @dataProvider isFileUploadCompleteProvider
     */
    public function testIsFileUploadComplete($filename,$identifier, $chunkSize, $totalSize, $expected)
    {
        $this->resumbable = new Resumable($this->request,$this->response);
        $this->resumbable->tempFolder ='test';
        $this->assertEquals($expected, $this->resumbable->isFileUploadComplete($filename, $identifier, $chunkSize, $totalSize));
    }

    public function testIsChunkUploaded()
    {
        $this->resumbable = new Resumable($this->request,$this->response);
        $this->resumbable->tempFolder ='test';
        $identifier = 'files';
        $filename = 'mock.png';
        $this->assertTrue($this->resumbable->isChunkUploaded($identifier,$filename,1));
        $this->assertFalse($this->resumbable->isChunkUploaded($identifier,$filename,10));
    }

    public function testTmpChunkDir()
    {
        $this->resumbable = new Resumable($this->request,$this->response);
        $this->resumbable->tempFolder ='test';
        $identifier = 'mock-identifier';
        $expected = $this->resumbable->tempFolder.DIRECTORY_SEPARATOR.$identifier;
        $this->assertEquals($expected, $this->resumbable->tmpChunkDir($identifier));
        $this->assertFileExists($expected);
        rmdir($expected);
    }

    public function testTmpChunkFile()
    {
        $this->resumbable = new Resumable($this->request,$this->response);
        $filename = 'mock-file.png';
        $chunkNumber = 1;
        $expected = $filename.'.part'.$chunkNumber;
        $this->assertEquals($expected, $this->resumbable->tmpChunkFilename($filename,$chunkNumber));
    }

    public function testCreateFileFromChunks()
    {
        $files = array(
            'test/files/mock.png.part1',
            'test/files/mock.png.part2',
            'test/files/mock.png.part3',
        );
        $totalFileSize = array_sum(array(
            filesize('test/files/mock.png.part1'),
            filesize('test/files/mock.png.part2'),
            filesize('test/files/mock.png.part3')
        ));
        $destFile = 'test/files/5.png';

        $this->resumbable = new Resumable($this->request,$this->response);
        $this->resumbable->createFileFromChunks($files, $destFile);
        $this->assertFileExists($destFile);
        $this->assertEquals($totalFileSize, filesize($destFile));
        unlink('test/files/5.png');
    }

    public function testMoveUploadedFile()
    {
        $destFile = 'test/files/4.png';
        $this->resumbable = new Resumable($this->request,$this->response);
        $this->resumbable->moveUploadedFile('test/files/mock.png.part1', $destFile);
        $this->assertFileExists($destFile);
        unlink($destFile);
    }
}
