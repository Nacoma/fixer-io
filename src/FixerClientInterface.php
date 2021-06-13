<?php

namespace Nacoma\Fixer;

interface FixerClientInterface
{
    /**
     * @throws FixerException
     * @param string $path
     * @param array $query
     * @return array
     */
    public function get(string $path, array $query = []): array;
}
