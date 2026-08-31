<?php

namespace App\Filament\Concerns;

use App\Enums\SectionType;
use Illuminate\Database\Eloquent\Builder;

/**
 * Used by the thin per-type Filament resources that all share the `Section`
 * model/table (Testimonial, FaqGroup) — scopes the resource's query to its
 * fixed type, and exposes that type to `SetsSectionTypeOnCreate` so both
 * concerns read from a single declared source of truth.
 */
trait ScopedToSectionType
{
    abstract public static function sectionType(): SectionType;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->ofType(static::sectionType());
    }
}
