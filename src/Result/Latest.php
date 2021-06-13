<?php

namespace Nacoma\Fixer\Result;

use DateTimeImmutable;
use Exception;

class Latest
{
    public function __construct(
        public DateTimeImmutable $date,
        public int $timestamp,
        public string $base,
        public array $rates,
    ) {
        //
    }

    /**
     * @throws Exception
     */
    public static function fromArray(array $array): self
    {
        return new self(
            date: new DateTimeImmutable($array['date']),
            timestamp: $array['timestamp'],
            base: $array['base'],
            rates: $array['rates'],
        );
    }
}
