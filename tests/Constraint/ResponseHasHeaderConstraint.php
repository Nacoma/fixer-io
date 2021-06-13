<?php

namespace Tests\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\ResponseInterface;

class ResponseHasHeaderConstraint extends Constraint
{
    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    protected function matches($other): bool
    {
        return array_key_exists($other, $this->response->getHeaders());
    }

    public function toString(): string
    {
        return 'was present in ' . json_encode($this->response->getHeaders(), JSON_PRETTY_PRINT);
    }
}
