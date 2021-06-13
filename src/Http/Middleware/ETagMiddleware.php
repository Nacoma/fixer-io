<?php

namespace Nacoma\Fixer\Http\Middleware;

use Closure;
use Nacoma\Fixer\ClientMiddlewareInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class ETagMiddleware implements ClientMiddlewareInterface
{
    private const FMT = 'D, d M Y H:i:s T';

    private const DISALLOWED_CHARS = ['{', '}', '(', ')', '/', '\\', '@'];

    public function __construct(
        private CacheInterface $cache,
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
        //
    }

    /**
     * @param Closure(RequestInterface): ResponseInterface $next
     * @throws InvalidArgumentException
     */
    public function handle(RequestInterface $request, Closure $next): ResponseInterface
    {
        $item = $this->cache->get(self::key($request));

        if ($item === null) {
            $res = $next($request);

            $item = self::deconstructItem($res);

            $this->cache->set(self::key($request), $item);

            return $this->makeResponse($item);
        }

        if (isset($item[0]['ETag'][0]) && isset($item[0]['Date'][0])) {
            $eTag = $item[0]['ETag'][0];
            $date = $item[0]['Date'][0];

            $request = $request
                ->withAddedHeader('If-None-Match', $eTag)
                ->withAddedHeader('If-Modified-Since', $date);
        }

        $res = $next($request);

        if ($res->getStatusCode() === 304) {
            return $this->makeResponse($item);
        }

        $item = self::deconstructItem($res);

        $this->cache->set(self::key($request), $item);

        return $this->makeResponse($item);
    }

    private function makeResponse(array $item): ResponseInterface
    {
        [$headers, $status, $reason, $body] = $item;

        $res = $this->responseFactory->createResponse($status, $reason);

        foreach ($headers as $headerName => $headerValue) {
            $res = $res->withAddedHeader($headerName, $headerValue);
        }

        $stream = $this->streamFactory->createStream($body);
        $stream->rewind();

        return $res->withBody($stream);
    }

    private static function key(RequestInterface $request): string
    {
        $uri = $request->getUri();

        return sprintf(
            'fixer_%s_%s_%s',
            str_replace(self::DISALLOWED_CHARS, '-', $uri->getHost()),
            str_replace(self::DISALLOWED_CHARS, '-', $uri->getPath()),
            $uri->getQuery()
        );
    }

    private static function deconstructItem(ResponseInterface $response): array
    {
        return [
            $response->getHeaders(),
            $response->getStatusCode(),
            $response->getReasonPhrase(),
            $response->getBody()->getContents(),
        ];
    }
}
