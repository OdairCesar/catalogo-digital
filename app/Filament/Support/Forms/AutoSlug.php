<?php

namespace App\Filament\Support\Forms;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/**
 * Wires a title/name field so it auto-fills the slug field while the user
 * types, without overwriting a slug the user has already customized by hand.
 */
final class AutoSlug
{
    public static function attach(TextInput $field, string $slugField = 'slug'): TextInput
    {
        return $field
            ->live(onBlur: true)
            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) use ($slugField): void {
                if (($get($slugField) ?? '') !== Str::slug((string) $old)) {
                    return;
                }

                $set($slugField, Str::slug((string) $state));
            });
    }
}
