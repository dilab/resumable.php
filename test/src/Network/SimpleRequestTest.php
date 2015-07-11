<?php
namespace Dilab\Network;

use Dilab\Network\Request;

/**
 * Class SimpleRequestTest
 * @package Dilab\Network
 * @property $request Request
 */
class SimpleRequestTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->request = new SimpleRequest();
    }

    public function tearDown()
    {
        unset($this->request);
        parent::tearDown();
    }


   public function testIsPost()
   {
       $_POST = array(
           'resumableChunkNumber'=> 3,
           'resumableTotalChunks'=> 600,
           'resumableChunkSize'=>  200,
           'resumableIdentifier'=> 'identifier',
           'resumableFilename'=> 'mock.png',
           'resumableRelativePath'=> 'upload',
       );
       $this->assertTrue($this->request->is('post'));
       unset($_POST);
   }

   public function testIsGet()
   {
       $_GET = array(
           'resumableChunkNumber'=> 3,
           'resumableTotalChunks'=> 600,
           'resumableChunkSize'=>  200,
           'resumableIdentifier'=> 'identifier',
           'resumableFilename'=> 'mock.png',
           'resumableRelativePath'=> 'upload',
       );
       $this->assertTrue($this->request->is('get'));
       unset($_GET);
   }

   public function testData()
   {
       $data = array(
           'resumableChunkNumber'=> 3,
           'resumableTotalChunks'=> 600,
           'resumableChunkSize'=>  200,
           'resumableIdentifier'=> 'identifier',
           'resumableFilename'=> 'mock.png',
           'resumableRelativePath'=> 'upload',
       );

       $_GET = $data;
       $_POST = $data;

       $this->assertEquals($data,$this->request->data('get'));
       $this->assertEquals($data,$this->request->data('post'));

       unset($_GET);
       unset($_POST);
   }

   public function testFile()
   {
       $file = array(
           'name'=> 'mock.png',
           'type'=> 'application/octet-stream',
           'tmp_name'=>  'test/files/mock.png.part3',
           'error'=> 0,
           'size'=> 1048576,
       );

       $_FILES['file'] = $file;
       $this->assertEquals($file,$this->request->file());
       unset($_FILES);
   }


}