<?php

namespace App\Filament\Support\Forms;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;

/**
 * Shared "extra fields" repeater used by every `Section`-backed Filament
 * form, so Cae can add ad-hoc label/value pairs on any section type. Rendered
 * on the public site by `<x-section.extra-fields>`.
 */
final class ExtraFieldsRepeater
{
    public static function make(): Repeater
    {
        return Repeater::make('extra_fields')
            ->label('Campos extras')
            ->schema([
                TextInput::make('label')->label('Rótulo')->required(),
                TextInput::make('value')->label('Valor')->required(),
            ])
            ->columns(2)
            ->collapsible()
            ->defaultItems(0)
            ->addActionLabel('Adicionar campo')
            ->columnSpanFull();
    }
}
