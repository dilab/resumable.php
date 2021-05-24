<?php
namespace Dilab\Network;

use Dilab\Network\Response;

class SimpleResponse implements Response
{
    /**
     * @param $statusCode
     * @return mixed
     */
    public function header($statusCode)
    {
        if (200==$statusCode) {
            return header($_SERVER["SERVER_PROTOCOL"]." 200 Ok");
        } else if (404==$statusCode) {
            return header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        }
        return header($_SERVER["SERVER_PROTOCOL"]." 204 No Content");
    }

}
