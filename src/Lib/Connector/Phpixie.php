<?php

namespace Codeception\Lib\Connector;

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;

class Phpixie extends Client
{

    public $module;

    /**
     * Constructor.
     *
     * @param \Codeception\Module\Phpixie $module
     */
    public function __construct($module)
    {
        $this->module = $module;

        $components = parse_url($this->module->config['url']);
        $server     = ['HTTP_HOST' => $components['host']];
    }

    /**
     * Execute a request.
     *
     * @param SymfonyRequest $request
     * @return Response
     */
    protected function doRequest($request)
    {


        var_dump($request); exit;
        /** @var $request BrowserKitRequest  **/
/*        $guzzleRequest = new Psr7Request(
            $request->getMethod(),
            $request->getUri(),
            $this->extractHeaders($request),
            $request->getContent()
        );

        $options = $this->requestOptions;
        $options['cookies'] = $this->extractCookies($guzzleRequest->getUri()->getHost());
        $multipartData = $this->extractMultipartFormData($request);

        if (!empty($multipartData)) {
            $options['multipart'] = $multipartData;
        }

        $formData = $this->extractFormData($request);
        if (empty($multipartData) and $formData) {
            $options['form_params'] = $formData;
        }

        try {
            $response = $this->client->send($guzzleRequest, $options);
        } catch (RequestException $e) {
            if (!$e->hasResponse()) {
                throw $e;
            }
            $response = $e->getResponse();
        }
        return $this->createResponse($response);*/
    }
}
