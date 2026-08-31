<?php

namespace App\Services\Products\Import;

/**
 * A single retrievable term from the existing catalog (a category name, an
 * attribute, an attribute value, or a brand already used by the company) —
 * the unit the RAG step matches spreadsheet column/cell text against.
 */
final readonly class CatalogTerm
{
    public function __construct(
        public string $type,
        public string $label,
        public ?int $id = null,
    ) {}
}
