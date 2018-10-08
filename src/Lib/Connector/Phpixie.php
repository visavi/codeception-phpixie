<?php

namespace Codeception\Lib\Connector;

use Project\Framework;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;

class Phpixie extends Client
{
    /**
     * @var \Project\Framework
     */
    protected $framework;

    /**
     * @var \PHPixie\HTTP
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
        $this->http      = $this->framework->builder()->components()->http();
        $this->module    = $module;

        $components = parse_url($this->module->config['url']);

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

        $_COOKIE = $request->getCookies();
        $_FILES  = $request->getFiles();

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
            '',
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
