<?php
namespace Dilab\Network;

use Dilab\Network\Response;

/**
 * Class SimpleResponseTest
 * @package Dilab\Network
 * @property $response Response
 */
class SimpleResponseTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
        $this->response = new SimpleResponse();
    }

    public function tearDown() : void
    {
        unset($this->response);
        parent::tearDown();
    }


    public static function headerProvider()
    {
        return [
            [404,404],
            [204,204],
            [200,200],
            [500,204],
        ];
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