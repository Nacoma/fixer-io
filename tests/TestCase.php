<?php

namespace Tests;

use Tests\Constraint\ResponseHasHeaderValuesConstraint;
use Tests\Constraint\ResponseHasHeaderConstraint;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Nacoma\Fixer\ExchangeFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\ResponseInterface;

abstract class TestCase extends BaseTestCase
{
    protected MockHandler $mockHandler;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler([]);

        $this->client = new Client([
            'handler' => HandlerStack::create($this->mockHandler)
        ]);

    }

    protected function loadResponse(string $name): string
    {
        return file_get_contents(__DIR__ . '/responses/' . $name);
    }
}
