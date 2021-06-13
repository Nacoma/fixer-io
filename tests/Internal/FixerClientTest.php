<?php

namespace Tests\Internal;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Nacoma\Fixer\Internal\FixerClient;
use Nacoma\Fixer\FixerException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Tests\TestCase;

/**
 * @uses \Nacoma\Fixer\ExchangeFactory::__construct
 * @covers \Nacoma\Fixer\Internal\FixerClient
 */
class FixerClientTest extends TestCase
{
    private FixerClient $fixerClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixerClient = new FixerClient(
            $this->client,
            new Psr17Factory(),
            new Psr17Factory(),
            'http',
            'localhost',
        );
    }

    /**
     * @test
     */
    public function uri(): void
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode(['success' => true])),
        );

        $this->fixerClient->get('any', ['test' => 'tset']);

        $request = $this->mockHandler->getLastRequest();

        $this->assertEquals('http', $request?->getUri()?->getScheme());
        $this->assertEquals('test=tset', $request?->getUri()?->getQuery());
        $this->assertEquals('any', $request?->getUri()?->getPath());
    }

    /**
     * @test
     */
    public function returns_successful_responses(): void
    {
        $payload = [
            'success' => true,
            'data' => [
                'test1' => 'one',
                'test2' => 1
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [],  json_encode($payload))
        );

        $response = $this->fixerClient->get('some-path');

        $this->assertEquals($payload, $response);
    }

    /**
     * @test
     */
    public function fixer_exception_when_not_successful(): void
    {
        $payload = [
            'success' => false,
            'error' => [
                'info' => 'messed up',
                'code' => 9009,
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($payload)),
        );

        $this->expectException(FixerException::class);
        $this->expectExceptionMessage('messed up');
        $this->expectExceptionCode(9009);

        $this->fixerClient->get('test');
    }

    /**
     * @test
     */
    public function throws_on_non_200_status_codes(): void
    {
        $this->mockHandler->append(
            new Response(422, [], 'something')
        );

        $this->expectException(FixerException::class);

        $this->fixerClient->get('some-path');
    }

    /**
     * @test
     */
    public function converts_psr_exception_to_fixer_exception(): void
    {
        $this->mockHandler->append(
            new RequestException('something happened', new Request('GET', 'test'))
        );

        $this->expectException(FixerException::class);

        $this->fixerClient->get('test');
    }


    /**
     * @test
     * @covers \Nacoma\Fixer\Client\FixerClient::get
     */
    public function invalid_json(): void
    {
        $this->mockHandler->reset();;
        $this->mockHandler->append(
            new Response(200, [], '{{bad json')
        );

        $this->expectException(FixerException::class);
        $this->expectExceptionMessage('Syntax error');

        $this->fixerClient->get('whatever');
    }


    /**
     * @test
     * @covers \Nacoma\Fixer\Client\FixerClient::get
     */
    public function error_response(): void
    {
        $this->mockHandler->reset();
        $this->mockHandler->append(
            new Response(200, [], $this->loadResponse('symbols_error_101.json')),
        );

        $this->expectException(FixerException::class);
        $this->expectExceptionMessage("You have not supplied an API Access Key. [Required format: access_key=YOUR_ACCESS_KEY]");

        $this->fixerClient->get('whatever');
    }
}
