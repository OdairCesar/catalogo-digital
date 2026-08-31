<?php

namespace App\Filament\Support\Tables;

use App\Enums\PageStatus;
use App\Filament\Support\Actions\BulkPublishStatusActions;
use App\Filament\Support\Actions\TogglePublishStatusAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\SelectFilter;

/**
 * Shared column/filter set for the two "landing page" resources (LandingPage
 * and ServiceClusterLandingPage), which only differ in their parent
 * relationship column and filter.
 */
final class LandingPageTableColumns
{
    /**
     * @return array<int, TextColumn>
     */
    public static function common(): array
    {
        return [
            TextColumn::make('city.name')
                ->label('Cidade')
                ->searchable()
                ->sortable(),
            TextColumn::make('slug')
                ->label('Slug')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('status')
                ->label('Status')
                ->badge(),
            TextColumn::make('meta_title')
                ->label('Meta título')
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('custom_h1')
                ->label('H1 customizado')
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                ->label('Atualizado em')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * @return array<int, BaseFilter>
     */
    public static function commonFilters(): array
    {
        return [
            SelectFilter::make('city_id')
                ->label('Cidade')
                ->relationship('city', 'name')
                ->searchable(),
            SelectFilter::make('status')
                ->label('Status')
                ->options(PageStatus::class),
        ];
    }

    public static function toggleStatusAction(): Action
    {
        return TogglePublishStatusAction::make(PageStatus::class);
    }

    /**
     * @return array<int, BulkAction>
     */
    public static function bulkStatusActions(): array
    {
        return BulkPublishStatusActions::make(PageStatus::class);
    }
}
