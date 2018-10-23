<?php

namespace Codeception\Lib\Connector;

use Project\Framework;
use PHPixie\HTTP;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;

class Phpixie extends Client
{
    use Shared\PhpSuperGlobalsConverter;

    /**
     * @var Framework
     */
    protected $framework;

    /**
     * @var HTTP
     */
    protected $http;

    /**
     * @var \Codeception\Module\Phpixie
     */
    protected $module;

    /**
     * Constructor.
     *
     * @param \Codeception\Module\Phpixie $module
     */
    public function __construct($module)
    {
        $this->framework = new Framework();
        $this->builder   = $this->framework->builder();
        $this->http      = $this->builder->components()->http();
        $this->module    = $module;

        $baseHost = $this->builder
            ->configuration()
            ->httpConfig()
            ->get('translator.baseHost', 'http://localhost');

        if (strpos($baseHost, '//') === false) {
            $baseHost = '//' . $baseHost;
        }

        $components = parse_url($baseHost);

        if (array_key_exists('url', $this->module->config)) {
            $components = parse_url($this->module->config['url']);
        }

        $server = [
            'HTTP_HOST' => $components['host'],
        ];

        parent::__construct($server);
    }

    /**
     * Execute a request.
     *
     * @param BrowserKitRequest $request
     * @return Response
     */
    protected function doRequest($request)
    {
        $uri         = $request->getUri();
        $server      = $request->getServer();
        $method      = $request->getMethod();
        $pathString  = parse_url($uri, PHP_URL_PATH);
        $queryString = parse_url($uri, PHP_URL_QUERY);

        $_COOKIE  = $request->getCookies();
        $_FILES   = $this->remapFiles($request->getFiles());
        $_REQUEST = $this->remapRequestParameters($request->getParameters());
        $_POST    = $_GET = [];

        if (strtoupper($method) === 'GET') {
            $_GET = $_REQUEST;
        } else {
            $_POST = $_REQUEST;
        }

        $_SERVER = [
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD'  => $method,
            'REQUEST_URI'     => $pathString . $queryString,
            'HTTPS'           => $server['HTTPS'] ? 'on' : 'off',
        ];

        $_SERVER += $server;

        $uri = $this->http->messages()->sapiUri($_SERVER);

        $serverRequest = $this->http->messages()->serverRequest(
            $_SERVER['SERVER_PROTOCOL'],
            $_SERVER,
            $_POST,
            $method,
            $uri,
            $_SERVER,
            $queryString,
            null,
            $_COOKIE,
            $_FILES
        );

        $response = $this->framework->processHttpServerRequest($serverRequest);

        return new Response(
            $response->getBody(),
            $response->getStatusCode(),
            $response->getHeaders()
        );
    }
}
