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
    public $http;

    /**
     * @var \Codeception\Module\Phpixie
     */
    public $module;

    /**
     * Constructor.
     *
     * @param \Codeception\Module\Phpixie $module
     */
    public function __construct($module)
    {
        $this->framework = new Framework();
        $this->builder   = new \Project\App\AppBuilder($this->framework->builder());
        $this->http      = $this->builder->components()->http();
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
        $pathString  = parse_url($uri, PHP_URL_PATH);
        $queryString = parse_url($uri, PHP_URL_QUERY);

        $_COOKIE = $request->getCookies();
        $_FILES  = $request->getFiles();

        $_SERVER = [
            'REQUEST_METHOD' => $request->getMethod(),
            'REQUEST_URI'    => $pathString . $queryString,
            'HTTPS'          => $server['HTTPS'] ? 'on' : 'off',
        ];

        $_SERVER += $server;

        $uri = $this->http->messages()->sapiUri($_SERVER);

        $serverRequest = $this->http->messages()->serverRequest(
            'HTTP/1.1',
            $_SERVER,
            '',
            $request->getMethod(),
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
