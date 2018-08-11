<?php

namespace Intec\Router;

use Slim\Http\Request as SlimRequest;

final class Request extends SlimRequest
{

    private $urlParams = [];

    public function getPostParams()
    {
        // trigger_error('Deprecated function called, use \'getParsedBody()\' instead.', E_USER_DEPRECATED);
        return $this->getParsedBody();
    }

    public function getFilesParams()
    {
        // trigger_error('Deprecated function called, use \'getUploadedFiles()\' instead.', E_USER_DEPRECATED);
        return $_FILES;
    }

    public function setUrlParams(array $params)
    {
        $this->urlParams = $params;
    }

    public function getUrlParams()
    {
        // trigger_error('Deprecated function called, use the 3rd parameter in controller method \'public function myAction($request, $response, $params)\' instead.', E_USER_DEPRECATED);
        return $this->urlParams;
    }

    public function isXmlHttpRequest()
    {
        // trigger_error('Deprecated function called, use substr(\'application/json\', $request->getHeaderLine(\'accept\') instead', E_USER_DEPRECATED);
        return substr('application/json', $this->getHeaderLine('accept'));
    }
}
