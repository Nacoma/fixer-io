<?php

namespace Nacoma\Fixer\Result;

class FluctuationItem
{
    public function __construct(
        public float $startRate,
        public float $endRate,
        public float $change,
        public float $changePct,
    )
    {
        //
    }
}
