<?php

namespace App\Services\Products\Import;

final readonly class CategoryResolution
{
    public function __construct(
        public string $name,
        public ?int $existingCategoryId,
    ) {}
}
