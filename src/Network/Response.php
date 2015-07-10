<?php
namespace Dilab\Network;

interface Response {

    /**
     * @param $statusCode
     * @return mixed
     */
    public function header($statusCode);

}