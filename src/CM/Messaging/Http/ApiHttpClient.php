<?php

namespace CM\Messaging\Http;

use CM\Messaging\Config;
use CM\Messaging\Exception\BadRequestException;
use CM\Messaging\Response\Response;
use GuzzleHttp\Psr7\Request;
use Http\Client\Exception\HttpException;
use Http\Client\HttpClient;

/**
 * Class ApiHttpClient
 *
 * @package CM\Messaging\Http
 */
class ApiHttpClient
{
    /**
     * Header for http request
     *
     * @var array
     */
    private $header;

    /**
     * Http client used to preform request
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * ApiHttpClient constructor.
     *
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->header     = $this->buildHeader();
    }

    /**
     * Generate request header with SDK data
     *
     * @return array
     */
    private function buildHeader()
    {
        return [
            'Content-Type' => 'application/json',
            'LANGUAGE'     => Config::LANGUAGE,
            'SDK_VERSION'  => Config::SDK_VERSION,
            'HTTP_CLIENT'  => get_class($this->httpClient)
        ];
    }

    /**
     * @param string $endpoint
     * @param string $body
     *
     * @return Response
     * @throws BadRequestException
     */
    public function postRequest($endpoint, $body)
    {
        try {
            $request  = new Request('POST', $endpoint, $this->header, json_encode($body));
            $response = $this->httpClient->sendRequest($request);

            return new Response($response);
        } catch (HttpException $e) {
            if ($e->getCode() === 400) {
                throw new BadRequestException($e->getMessage(), $e->getRequest(), $e->getResponse());
            }

            throw $e;
        }
    }
}