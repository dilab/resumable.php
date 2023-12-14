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
        if($statusCode >= 500) {
            $statusCode = 204;
        }
        if (!in_array($statusCode, [200,201,204,404])) {
            $statusCode = 404;
        }
        http_response_code($statusCode);
        return $statusCode;
    }

}
