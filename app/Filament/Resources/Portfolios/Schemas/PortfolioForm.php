<?php

namespace App\Filament\Resources\Portfolios\Schemas;

use App\Enums\PageStatus;
use App\Filament\Support\Forms\AutoSlug;
use App\Filament\Support\Forms\CloudinaryImageUpload;
use App\Filament\Support\Forms\ExtraFieldsRepeater;
use App\Models\Service;
use App\Services\Portfolio\PortfolioCopyGenerator;
use App\Services\Portfolio\PortfolioCoverImageGenerator;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Throwable;

class PortfolioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('service_id')
                    ->label('Serviço')
                    ->relationship('service', 'title')
                    ->searchable()
                    ->preload(),
                AutoSlug::attach(
                    TextInput::make('title')
                        ->required(),
                ),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Usado na URL, ex: /portfolio/{slug}.'),
                TextInput::make('data.external_url')
                    ->label('Link do projeto')
                    ->url()
                    ->live(onBlur: true)
                    ->belowContent(
                        Action::make('generatePortfolioCopy')
                            ->label('Gerar texto')
                            ->icon(Heroicon::OutlinedSparkles)
                            ->disabled(fn (Get $get): bool => blank($get('data.external_url')))
                            ->action(function (Get $get, Set $set, PortfolioCopyGenerator $generator): void {
                                set_time_limit(120);

                                try {
                                    $copy = $generator->generate($get->string('data.external_url'), self::resolveServiceTitle($get));
                                } catch (Throwable $exception) {
                                    report($exception);

                                    Notification::make()
                                        ->title('Não foi possível gerar o texto')
                                        ->body('Tente novamente em instantes.')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $set('title', $copy['title']);
                                $set('excerpt', $copy['excerpt']);

                                Notification::make()
                                    ->title('Texto gerado com sucesso')
                                    ->success()
                                    ->send();
                            }),
                    ),
                Textarea::make('excerpt')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('content')
                    ->columnSpanFull(),
                CloudinaryImageUpload::make('image')
                    ->label('Imagem de capa')
                    ->belowContent(
                        Action::make('generatePortfolioCoverImage')
                            ->label('Gerar imagem')
                            ->icon(Heroicon::OutlinedSparkles)
                            ->disabled(fn (Get $get): bool => blank($get('title')))
                            ->action(function (Get $get, Set $set, PortfolioCoverImageGenerator $generator): void {
                                set_time_limit(120);

                                try {
                                    $path = $generator->generate(
                                        title: $get->string('title'),
                                        excerpt: $get->string('excerpt', isNullable: true),
                                        serviceTitle: self::resolveServiceTitle($get),
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

                                $set('image', $path);

                                Notification::make()
                                    ->title('Imagem gerada com sucesso')
                                    ->success()
                                    ->send();
                            }),
                    ),
                TextInput::make('meta_title'),
                TextInput::make('meta_description'),
                TextInput::make('canonical'),
                TextInput::make('robots')
                    ->default('index,follow'),
                Select::make('status')
                    ->options(PageStatus::class)
                    ->default(PageStatus::Draft)
                    ->required(),
                ExtraFieldsRepeater::make(),
            ]);
    }

    private static function resolveServiceTitle(Get $get): ?string
    {
        $serviceId = $get('service_id');

        if (! is_int($serviceId) && ! is_string($serviceId)) {
            return null;
        }

        return Service::query()->find($serviceId)?->title;
    }
}
