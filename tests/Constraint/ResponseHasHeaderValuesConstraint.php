<?php

namespace Tests\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\ResponseInterface;

class ResponseHasHeaderValuesConstraint extends Constraint
{
    public function __construct(private ResponseInterface $response)
    {
    }

    protected function matches($other): bool
    {
        $headers = $this->response->getHeaders();

        foreach ($other as $name => $value) {
            if (!array_key_exists($name, $headers)) {
                return false;
            }

            if (!in_array($value, $headers[$name])) {
                return false;
            }
        }

        return true;
    }

    public function toString(): string
    {
        return 'did not match ' . json_encode($this->response->getHeaders(), JSON_PRETTY_PRINT);
    }
}
