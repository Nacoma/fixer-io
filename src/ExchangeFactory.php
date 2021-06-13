<?php

namespace Nacoma\Fixer;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class ExchangeFactory
{
    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private UriFactoryInterface $uriFactory,
        private string $accessKey,
        private string $scheme = 'http',
        private string $host = 'data.fixer.io',
    )
    {}

    public function create(?string $base = null, ?array $symbols = []): Exchange
    {
        $fixerClient = new Internal\FixerClient(
            client: $this->client,
            requestFactory: $this->requestFactory,
            uriFactory: $this->uriFactory,
            scheme: $this->scheme,
            host: $this->host,
        );

        return new Exchange($fixerClient, $this->accessKey, $base, $symbols);
    }
}
