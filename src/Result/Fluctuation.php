<?php

namespace Nacoma\Fixer\Result;

use DateTimeImmutable;
use Exception;

/**
 * @property array<string, FluctuationItem> $rates
 */
class Fluctuation
{
    public function __construct(
        public DateTimeImmutable $startDate,
        public DateTimeImmutable $endDate,
        public string $base,
        public array $rates,
    ) {
    }

    /**
     * @throws Exception
     */
    public static function fromArray(array $rates): self
    {
        $items = [];

        foreach ($rates['rates'] as $key => $datum) {
            $items[$key] = new FluctuationItem(
                startRate: $datum['start_rate'],
                endRate: $datum['end_rate'],
                change: $datum['change'],
                changePct: $datum['change_pct'],
            );
        }

        return new self(
            startDate: new DateTimeImmutable($rates['start_date']),
            endDate: new DateTimeImmutable($rates['end_date']),
            base: $rates['base'],
            rates: $items,
        );
    }
}
