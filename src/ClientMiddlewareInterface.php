<?php

namespace Nacoma\Fixer;

use Closure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ClientMiddlewareInterface
{
    /**
     * @param Closure(RequestInterface): ResponseInterface $next
     */
    public function handle(RequestInterface $request, Closure $next): ResponseInterface;
}
