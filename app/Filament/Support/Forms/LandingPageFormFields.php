<?php

namespace App\Filament\Support\Forms;

use App\Enums\PageStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;

/**
 * Shared field set for the two "landing page" resources (LandingPage and
 * ServiceClusterLandingPage), which only differ in their parent relationship
 * select and the wording of the slug helper text.
 */
final class LandingPageFormFields
{
    /**
     * @return array<int, Component>
     */
    public static function common(string $slugHelperText): array
    {
        return [
            TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->helperText($slugHelperText),
            TextInput::make('meta_title')
                ->helperText('Deixe em branco para usar o título gerado automaticamente.'),
            TextInput::make('meta_description')
                ->helperText('Deixe em branco para usar a descrição gerada automaticamente.'),
            TextInput::make('canonical'),
            TextInput::make('robots')
                ->required()
                ->default('index,follow'),
            TextInput::make('custom_h1')
                ->helperText('Sobrescreve o H1 composto automaticamente para esta página específica.'),
            TextInput::make('custom_subtitle'),
            Textarea::make('custom_intro')
                ->columnSpanFull(),
            TextInput::make('custom_cta'),
            Select::make('status')
                ->options(PageStatus::class)
                ->default(PageStatus::Draft)
                ->required(),
        ];
    }
}
