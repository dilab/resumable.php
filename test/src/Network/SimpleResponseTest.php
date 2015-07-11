<?php
namespace Dilab\Network;

use Dilab\Network\Response;

/**
 * Class SimpleResponseTest
 * @package Dilab\Network
 * @property $response Response
 */
class SimpleResponseTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->response = new SimpleResponse();
    }

    public function tearDown()
    {
        unset($this->response);
        parent::tearDown();
    }


    public function headerProvider()
    {
        return array(
            array(404,404),
            array(200,200),
            array(500,404),
        );
    }

    /**
     * @runInSeparateProcess
     * @dataProvider headerProvider
     */
    public function testHeader($statusCode, $expectd)
    {
       $this->response->header($statusCode);
       $this->assertEquals($expectd, http_response_code());

    }

}