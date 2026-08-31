<?php

namespace App\Services\Products\Import;

final readonly class AttributeValueResolution
{
    public function __construct(
        public string $attributeName,
        public string $value,
        public ?int $existingAttributeValueId,
    ) {}
}
