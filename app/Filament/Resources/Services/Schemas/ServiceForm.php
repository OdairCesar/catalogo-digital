<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\PageStatus;
use App\Filament\Support\Forms\AutoSlug;
use App\Filament\Support\Forms\CloudinaryImageUpload;
use App\Services\Landing\ServiceHeroImageGenerator;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Throwable;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Usado na URL, ex: /servicos/{slug} e nas landing pages {slug}-em-{cidade}.'),
                AutoSlug::attach(
                    TextInput::make('name')
                        ->required(),
                ),
                TextInput::make('title')
                    ->required()
                    ->live(onBlur: true),
                TextInput::make('subtitle')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TagsInput::make('benefits')
                    ->required()
                    ->columnSpanFull(),
                Repeater::make('faq')
                    ->schema([
                        TextInput::make('question')->required(),
                        Textarea::make('answer')->required(),
                    ])
                    ->itemLabel(fn (array $state): ?string => is_string($state['question'] ?? null) ? $state['question'] : null)
                    ->collapsible()
                    ->columnSpanFull(),
                TagsInput::make('keywords')
                    ->required()
                    ->columnSpanFull(),
                CloudinaryImageUpload::make('hero_image')
                    ->label('Imagem do hero')
                    ->belowContent(
                        Action::make('generateHeroImage')
                            ->label('Gerar imagem')
                            ->icon(Heroicon::OutlinedSparkles)
                            ->disabled(fn (Get $get): bool => blank($get('title')))
                            ->action(function (Get $get, Set $set, ServiceHeroImageGenerator $generator): void {
                                // gpt-image-1 generations routinely take 10-30s; guard against
                                // hitting a lower default max_execution_time mid-request.
                                set_time_limit(120);

                                $benefits = $get('benefits');

                                try {
                                    $path = $generator->generate(
                                        title: $get->string('title'),
                                        subtitle: $get->string('subtitle', isNullable: true),
                                        description: $get->string('description', isNullable: true),
                                        benefits: is_array($benefits)
                                            ? array_values(array_filter($benefits, fn (mixed $value): bool => is_string($value)))
                                            : [],
                                    );
                                } catch (Throwable $exception) {
                                    report($exception);

                                    Notification::make()
                                        ->title('Não foi possível gerar a imagem')
                                        ->body('Tente novamente em instantes.')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $set('hero_image', $path);

                                Notification::make()
                                    ->title('Imagem gerada com sucesso')
                                    ->success()
                                    ->send();
                            }),
                    ),
                TextInput::make('icon'),
                Select::make('status')
                    ->options(PageStatus::class)
                    ->default(PageStatus::Draft)
                    ->required(),
            ]);
    }
}
