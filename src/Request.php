<?php


namespace Intec\Router;

class Request
{
    protected $queryParams;
    protected $postParams;
    protected $urlParams;
    protected $isXHttp;

    const XML_HTTP_REQUEST = 'xmlhttprequest';

    public function __construct()
    {
        $this->queryParams = [];
        $this->postParams = [];
        $this->urlParams = [];
        $this->parsePost();
        $this->isXHttp = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == self::XML_HTTP_REQUEST;
    }

    public function parseQueryParams($queryString)
    {
        parse_str($queryString, $this->queryParams);
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    private function parsePost()
    {
        $this->postParams = $_POST;
    }

    public function getPostParams()
    {
        return $this->postParams;
    }

    public function parseUrlParams($params)
    {
        $this->urlParams = $params;
    }

    public function getUrlParams()
    {
        return $this->urlParams;
    }

    public function isXmlHttpRequest()
    {
        return $this->isXHttp;
    }
}
