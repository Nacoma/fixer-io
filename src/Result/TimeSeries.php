<?php

namespace Nacoma\Fixer\Result;

use DateTimeImmutable;
use Exception;

class TimeSeries
{
    public function __construct(
        public DateTimeImmutable $startDate,
        public DateTimeImmutable $endDate,
        public string $base,
        public array $rates,
    ) {
        //
    }

    /**
     * @throws Exception
     */
    public static function fromArray(array $rates): self
    {
        return new self(
            startDate: new DateTimeImmutable($rates['start_date']),
            endDate: new DateTimeImmutable($rates['end_date']),
            base: $rates['base'],
            rates: $rates['rates']
        );
    }
}
