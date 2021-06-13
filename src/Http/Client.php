<?php

namespace Nacoma\Fixer\Http;

use Nacoma\Fixer\ClientMiddlewareInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Client implements ClientInterface
{
    private ClientInterface $client;

    /**
     * @var ClientMiddlewareInterface[]
     */
    private array $middleware;

    public function __construct(ClientInterface $client, array $middleware = [])
    {
        $this->client = $client;
        $this->middleware = $middleware;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $chain = function (RequestInterface $request): ResponseInterface {
            return $this->client->sendRequest($request);
        };

        foreach ($this->middleware as $middleware) {
            $chain = function (RequestInterface $request) use ($middleware, $chain): ResponseInterface {
                return $middleware->handle($request, $chain);
            };
        }

        return $chain($request);
    }
}
