<?php

namespace Intec\Router;

use Slim\Http\Response as SlimResponse;

final class Response extends SlimResponse
{
    public function json($code = 200, $message = '', array $data = [])
    {
        return $this->withJson([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}