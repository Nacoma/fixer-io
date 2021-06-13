<?php

namespace Nacoma\Fixer\Result;

class ConvertInfo
{
    public function __construct(
        public int $timestamp,
        public float $rate,
    )
    {
        //
    }
}
