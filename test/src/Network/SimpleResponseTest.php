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


    public function testHeader()
    {

    }

}