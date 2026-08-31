<?php

namespace App\Filament\Support\Forms;

use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;

final class MoneyInput
{
    public static function make(string $name): TextInput
    {
        return TextInput::make($name)
            ->mask(RawJs::make('$money($input)'))
            ->stripCharacters(',')
            ->numeric()
            ->prefix('R$');
    }
}
