<?php

namespace App\Services\Products\Import;

final readonly class ProductImportResult
{
    /**
     * @param  array<int, string>  $errors
     */
    public function __construct(
        public int $created,
        public int $updated,
        public int $skipped,
        public array $errors,
    ) {}

    /**
     * @return array{created: int, updated: int, skipped: int, errors: array<int, string>}
     */
    public function toArray(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }
}
