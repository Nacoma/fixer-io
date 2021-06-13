<?php

namespace Nacoma\Fixer\Result;

class ConvertQuery
{
    public function __construct(
        public string $from,
        public string $to,
        public float|int $amount,
    )
    {
        //
    }
}
