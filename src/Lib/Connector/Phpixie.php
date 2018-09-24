<?php

namespace Codeception\Lib\Connector;

use PHPixie\HTTP\Messages\Message\Request\ServerRequest\SAPI;
use Project\Framework;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;

class Phpixie extends Client
{
    public $http;

    public $module;

    /**
     * Constructor.
     *
     * @param \Codeception\Module\Phpixie $module
     */
    public function __construct($module)
    {
        $framework    = new Framework();
        $this->http   = $framework->builder()->components()->http();
        $this->module = $module;

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
        $pathString  = parse_url($uri, PHP_URL_PATH);
        $queryString = parse_url($uri, PHP_URL_QUERY);

        $server = [
            'REQUEST_METHOD' => $request->getMethod(),
            'REQUEST_URI'    => $pathString . $queryString,
            'QUERY_STRING'   => $queryString,
            'HTTPS'          => null, // @TODO
        ];

        $server += $request->getServer();

        $serverRequest = new SAPI(
            $this->http->messages(),
            $server,
            ['get'    => 1], // @TODO
            ['post'   => 1], // @TODO
            $request->getCookies(),
            $request->getFiles(),
            $request->getParameters()
        );

        $uri = $this->http->request($serverRequest)->serverRequest()->getUri();

        return $this->http->messages()
            ->stream($uri)
            ->getContents();
    }
}
