<?php

namespace Nacoma\Fixer;

use DateTimeImmutable;
use Nacoma\Fixer\Result\Convert;
use Nacoma\Fixer\Result\Fluctuation;
use Nacoma\Fixer\Result\Historical;
use Nacoma\Fixer\Result\Latest;
use Nacoma\Fixer\Result\TimeSeries;

class Exchange
{
    private array $baseQuery = [];

    public function __construct(
        private FixerClientInterface $client,
        private string $accessKey,
        ?string $base = null,
        ?array $symbols = null
    ) {
        if (is_string($base)) {
            $this->baseQuery['base'] = $base;
        }

        if (is_array($symbols)) {
            $this->baseQuery['symbols'] = $symbols;
        }
    }

    /**
     * @return array<string, string>
     * @throws FixerException
     */
    public function symbols(): array
    {
        return $this->client->get('/api/symbols')['symbols'];
    }

    /**
     * @throws FixerException
     */
    public function latestRates(): Latest
    {
        $result = $this->client->get('/api/latest', array_merge(['access_key' => $this->accessKey], $this->baseQuery));

        return Latest::fromArray($result);
    }

    /**
     * @throws FixerException
     */
    public function historicalRates(DateTimeImmutable $date): Historical
    {
        $path = sprintf('/api/%s', $date->format('Y-m-d'));

        $result = $this->client->get($path, array_merge(['access_key' => $this->accessKey], $this->baseQuery));

        return Historical::fromArray($result);
    }

    public function timeSeries(DateTimeImmutable $startDate, DateTimeImmutable $endDate): TimeSeries
    {
        $result = $this->client->get('/api/timeseries', array_merge($this->baseQuery, [
            'access_key' => $this->accessKey,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ]));

        return TimeSeries::fromArray($result);
    }

    public function convert(string $from, string $to, int|float $amount, ?DateTimeImmutable $date = null): Convert
    {
        $query = [
            'access_key' => $this->accessKey,
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
        ];

        if ($date) {
            $query['date'] = $date->format('Y-m-d');
        }

        $result = $this->client->get('/api/convert', $query);

        return Convert::fromArray($result);
    }

    public function fluctuation(DateTimeImmutable $startDate, DateTimeImmutable $endDate): Fluctuation
    {
        $result = $this->client->get('/api/fluctuation', array_merge($this->baseQuery, [
            'access_key' => $this->accessKey,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ]));

        return Fluctuation::fromArray($result);
    }
}
