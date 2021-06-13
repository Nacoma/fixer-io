<?php

namespace Nacoma\Fixer\Internal;

use Nacoma\Fixer\FixerClientInterface;
use Nacoma\Fixer\FixerException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * @internal
 * @package Nacoma
 */
final class FixerClient implements FixerClientInterface
{
    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private UriFactoryInterface $uriFactory,
        private string $scheme = 'http',
        private string $host = 'data.fixer.io',
    ) {
        //
    }

    /**
     * @throws FixerException
     */
    public function get(string $path, array $query = []): array
    {
        $uri = $this->uriFactory->createUri($this->host)
            ->withScheme($this->scheme)
            ->withPath($path)
            ->withQuery(http_build_query($query));

        $request = $this->requestFactory->createRequest(
            'GET',
            $uri
        );

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new FixerException($e->getMessage());
        }

        $body = $response->getBody()->getContents();

        if ($response->getStatusCode() >= 400) {
            throw new FixerException($body);
        }

        /** @var array{success: bool, error: array{info: string, code: int}} $payload */
        $payload = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new FixerException(json_last_error_msg());
        }

        if ($payload['success'] === false) {
            throw new FixerException($payload['error']['info'], $payload['error']['code']);
        }

        return $payload;
    }
}
