<?php

namespace Tests\Http;

use GuzzleHttp\Psr7\Response;
use Nacoma\Fixer\ClientMiddlewareInterface;
use Nacoma\Fixer\Http\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Tests\TestCase;

/**
 * @covers \Nacoma\Fixer\Http\Client
 */
class ClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function returns_response(): void
    {
        $f = new Psr17Factory();

        $client = new Client($this->client);

        $this->mockHandler->append(
            new Response(100),
        );

        $response = $client->sendRequest($f->createRequest('GET', 'some'));

        $this->assertEquals(100, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function uses_middleware(): void
    {
        $f = new Psr17Factory();

        $middleware = \Mockery::mock(ClientMiddlewareInterface::class);
        $middleware->shouldReceive('handle')->withAnyArgs()->andReturn($f->createResponse(99));

        $client = new Client($this->client, [
            $middleware,
        ]);

        $response = $client->sendRequest($f->createRequest('GET', 'some'));

        $this->assertEquals(99, $response->getStatusCode());
    }
}
