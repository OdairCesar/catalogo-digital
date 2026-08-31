<?php

namespace App\Services\Products\Import;

final readonly class ParsedRow
{
    /**
     * @param  array<string, mixed>  $cells  keyed by header label
     */
    public function __construct(
        public int $rowNumber,
        public array $cells,
    ) {}
}
