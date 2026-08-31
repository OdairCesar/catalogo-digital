<?php

namespace App\Filament\Support\Tables;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

final class DateRangeFilter
{
    public static function make(string $column, string $label): Filter
    {
        return Filter::make($column)
            ->label($label)
            ->schema([
                DatePicker::make('from')->label('De'),
                DatePicker::make('until')->label('Até'),
            ])
            ->query(function (Builder $query, array $data) use ($column): Builder {
                $from = $data['from'] ?? null;
                $until = $data['until'] ?? null;

                if (is_string($from) && $from !== '') {
                    $query->whereDate($column, '>=', $from);
                }

                if (is_string($until) && $until !== '') {
                    $query->whereDate($column, '<=', $until);
                }

                return $query;
            });
    }
}
