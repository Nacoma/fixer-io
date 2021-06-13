<?php

namespace Nacoma\Fixer\Result;

use DateTimeImmutable;
use Exception;

class Convert
{
    public function __construct(
        public DateTimeImmutable $date,
        public float $result,
        public ConvertQuery $query,
        public ConvertInfo $info,
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
            result: $array['result'],
            query: new ConvertQuery(...$array['query']),
            info: new ConvertInfo(...$array['info']),
        );
    }
}
