<?php
namespace Dilab\Network;

interface Request {

    /**
     * @param $type get/post
     * @return boolean
     */
    public function is($type);

    /**
     * @param $requestType GET/POST
     * @return mixed
     */
    public function data($requestType);

    /**
     * @return FILES data
     */
    public function file();
}