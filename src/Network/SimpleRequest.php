<?php
namespace Dilab\Network;

use Dilab\Network\Request;

class SimpleRequest implements Request
{
    /**
     * @param $type get/post
     * @return boolean
     */
    public function is($type)
    {
        $type = strtolower($type);
    }

    /**
     * @param $requestType GET/POST
     * @return mixed
     */
    public function data($requestType)
    {
        // TODO: Implement data() method.
    }

    /**
     * @return FILES data
     */
    public function file()
    {
        // TODO: Implement file() method.
    }

}