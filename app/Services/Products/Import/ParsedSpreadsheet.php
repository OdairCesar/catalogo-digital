<?php

namespace App\Services\Products\Import;

final readonly class ParsedSpreadsheet
{
    /**
     * @param  array<int, string>  $headers
     * @param  array<int, ParsedRow>  $rows
     */
    public function __construct(
        public array $headers,
        public array $rows,
    ) {}
}
