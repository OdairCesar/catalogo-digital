<?php

namespace App\Filament\Concerns;

/**
 * Injects the owning resource's fixed `sectionType()` (see
 * `ScopedToSectionType`) into new records, so a Testimonial/FaqGroup created
 * through the admin is always saved with the right `type`.
 */
trait SetsSectionTypeOnCreate
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = static::getResource()::sectionType();

        return $data;
    }
}
