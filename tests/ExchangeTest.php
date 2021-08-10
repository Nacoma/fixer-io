<?php

namespace Tests;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Nacoma\Fixer\Exchange;
use Nacoma\Fixer\ExchangeFactory;
use Nacoma\Fixer\Http\Client;
use Nyholm\Psr7\Factory\Psr17Factory;

/**
 * @covers \Nacoma\Fixer\Exchange
 * @covers   \Nacoma\Fixer\Http\Client
 * @covers   \Nacoma\Fixer\ExchangeFactory
 * @uses   \Nacoma\Fixer\Internal\FixerClient
 */
class ExchangeTest extends TestCase
{
    private Exchange $exchange;

    private ExchangeFactory $exchangeFactory;

    /**
     * @test
     */
    public function correctHost(): void
    {
        $this->mockHandler->reset();
        $this->mockHandler->append(
            new Response(200, [], $this->loadResponse('symbols_success.json'))
        );

        $this->exchange->symbols();

        $request = $this->mockHandler->getLastRequest();

        $this->assertEquals('localhost', $request->getUri()->getHost());
    }

    /**
     * @test
     * @covers \Nacoma\Fixer\Exchange::symbols
     */
    public function symbols(): void
    {
        $this->mockHandler->reset();
        $this->mockHandler->append(
            new Response(200, [], $this->loadResponse('symbols_success.json')),
        );

        $symbols = $this->exchange->symbols();

        $this->assertArrayHasKey('USD', $symbols);
        $this->assertEquals('United States Dollar', $symbols['USD']);
    }

    /**
     * @test
     * @covers \Nacoma\Fixer\Exchange::latestRates
     * @covers \Nacoma\Fixer\Result\Latest
     */
    public function latest(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->loadResponse('latest_success.json')),
        );

        $result = $this->exchange->latestRates();

        $this->assertEquals(1519296206, $result->timestamp);
        $this->assertEquals('USD', $result->base);
        $this->assertEquals('2021-06-12', $result->date->format('Y-m-d'));
        $this->assertEquals(0.813399, $result->rates['EUR']);

        $request = $this->mockHandler->getLastRequest();

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertEquals('USD', $query['base']);
        $this->assertEquals(['JPY', 'EUR'], $query['symbols']);
    }

    /**
     * @test
     * @covers \Nacoma\Fixer\Exchange::historicalRates
     * @covers \Nacoma\Fixer\Result\Historical
     */
    public function historical(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->loadResponse('historical_success.json')),
        );

        $result = $this->exchange->historicalRates(new DateTimeImmutable());

        $this->assertEquals(1387929599, $result->timestamp);
        $this->assertEquals('GBP', $result->base);
        $this->assertEquals('2013-12-24', $result->date->format('Y-m-d'));
        $this->assertEquals(1.196476, $result->rates['EUR']);

        $request = $this->mockHandler->getLastRequest();

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertEquals('USD', $query['base']);
        $this->assertEquals(['JPY', 'EUR'], $query['symbols']);
    }

    /**
     * @test
     * @covers \Nacoma\Fixer\Exchange::timeSeries
     * @covers \Nacoma\Fixer\Result\TimeSeries
     */
    public function timeseries(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->loadResponse('time_series_success.json')),
        );

        $result = $this->exchange->timeSeries(new DateTimeImmutable(), new DateTimeImmutable());

        $this->assertEquals('EUR', $result->base);
        $this->assertEquals('2012-05-01', $result->startDate->format('Y-m-d'));
        $this->assertEquals('2012-05-03', $result->endDate->format('Y-m-d'));
        $this->assertEquals(1.322891, $result->rates['2012-05-01']['USD']);

        $request = $this->mockHandler->getLastRequest();

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertEquals('USD', $query['base']);
        $this->assertEquals(['JPY', 'EUR'], $query['symbols']);
    }

    /**
     * @test
     * @covers \Nacoma\Fixer\Exchange::fluctuation
     * @covers \Nacoma\Fixer\Result\Fluctuation
     * @covers \Nacoma\Fixer\Result\FluctuationItem
     */
    public function fluctuation(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->loadResponse('fluctuation_success.json')),
        );

        $result = $this->exchange->fluctuation(new DateTimeImmutable(), new DateTimeImmutable());

        $this->assertEquals('EUR', $result->base);
        $this->assertEquals('2018-02-25', $result->startDate->format('Y-m-d'));
        $this->assertEquals('2018-02-26', $result->endDate->format('Y-m-d'));
        $this->assertEquals(0.0635, $result->rates['JPY']->change);
    }

    /**
     * @test
     * @covers \Nacoma\Fixer\Exchange::convert
     * @covers \Nacoma\Fixer\Result\Convert
     * @covers \Nacoma\Fixer\Result\ConvertInfo
     * @covers \Nacoma\Fixer\Result\ConvertQuery
     */
    public function convert(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->loadResponse('convert_success.json')),
        );

        $result = $this->exchange->convert('GBP', 'JPY', 25, DateTimeImmutable::createFromFormat('Y-m-d', '2018-02-22'));

        $this->assertEquals('GBP', $result->query->from);
        $this->assertEquals('JPY', $result->query->to);
        $this->assertEquals(25, $result->query->amount);
        $this->assertEquals(1519328414, $result->info->timestamp);
        $this->assertEquals(148.972231, $result->info->rate);
        $this->assertEquals('2018-02-22', $result->date->format('Y-m-d'));
        $this->assertEquals(3724.305775, $result->result);

        $request = $this->mockHandler->getLastRequest();

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertEquals('GBP', $query['from']);
        $this->assertEquals('JPY', $query['to']);
        $this->assertEquals(25, $query['amount']);
        $this->assertEquals('2018-02-22', $query['date']);
    }

    /**
     * @test
     * @depends latest
     * @covers  \Nacoma\Fixer\ExchangeFactory
     * @uses    \Nacoma\Fixer\Internal\FixerClient
     * @uses    \Nacoma\Fixer\Result\Latest
     */
    public function factory(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->loadResponse('latest_success.json')),
            new Response(200, [], $this->loadResponse('latest_success.json')),
        );

        $exchange = $this->exchangeFactory->create('USD', ['EUR']);

        $exchange->latestRates();

        $request = $this->mockHandler->getLastRequest();
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertEquals('USD', $query['base']);
        $this->assertEquals(['EUR'], $query['symbols']);

        $exchange = $this->exchangeFactory->create();

        $exchange->latestRates();

        $request = $this->mockHandler->getLastRequest();
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertArrayNotHasKey('base', $query);
        $this->assertArrayNotHasKey('symbols', $query);
    }

    /**
     * @uses \Nacoma\Fixer\ExchangeFactory
     */
    protected function setUp(): void
    {
        parent::setUp();

        $client = new Client($this->client);

        $this->exchangeFactory = new ExchangeFactory(
            $client,
            new Psr17Factory(),
            new Psr17Factory(),
            'fake-key',
            'http',
            'localhost'
        );

        $this->exchange = $this->exchangeFactory->create(
            'USD',
            ['JPY', 'EUR']
        );
    }
}
