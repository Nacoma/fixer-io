<?php

namespace Tests;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use Tests\Constraint\ResponseHasHeaderConstraint;
use Tests\Constraint\ResponseHasHeaderValuesConstraint;

class Response
{
    public function __construct(private ResponseInterface $response)
    {
        //
    }

    public function assertHasHeaders($headers): self
    {
        if (is_string($headers)) {
            Assert::assertThat($headers, new ResponseHasHeaderConstraint($this->response));
        } else {
            Assert::assertThat($headers, new ResponseHasHeaderValuesConstraint($this->response));
        }

        return $this;
    }

    public function assertBody(string $body): self
    {
        Assert::assertEquals($body, (string)$this->response->getBody());

        return $this;
    }
}
