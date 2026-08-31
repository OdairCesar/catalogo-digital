<?php

namespace App\Filament\Resources\ServiceClusters\Schemas;

use App\Enums\ServiceClusterStatus;
use App\Filament\Support\Forms\AutoSlug;
use App\Filament\Support\Forms\CloudinaryImageUpload;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Services\ServiceCluster\ServiceClusterHeroImageGenerator;
use Filament\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Throwable;

class ServiceClusterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('service_id')
                    ->label('Serviço')
                    ->relationship('service', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Usado na URL, ex: /servicos/{servico}/{slug}.'),
                AutoSlug::attach(
                    TextInput::make('name')
                        ->required(),
                ),
                TextInput::make('title')
                    ->required(),
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
                    ->helperText('Deixe em branco para usar a imagem do serviço pai.')
                    ->belowContent(
                        Action::make('generateHeroImage')
                            ->label('Gerar imagem')
                            ->icon(Heroicon::OutlinedSparkles)
                            ->disabled(fn (Get $get): bool => blank($get('title')) || blank($get('service_id')))
                            ->action(function (Get $get, Set $set, ServiceClusterHeroImageGenerator $generator): void {
                                // this writes the image prompt via a text model before generating
                                // the image itself, so allow enough headroom for both AI calls.
                                set_time_limit(180);

                                $service = Service::query()->find($get('service_id'));

                                if (! $service instanceof Service) {
                                    Notification::make()
                                        ->title('Selecione um serviço primeiro')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $benefits = $get('benefits');

                                try {
                                    $path = $generator->generate(
                                        service: $service,
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
                Select::make('status')
                    ->options(ServiceClusterStatus::class)
                    ->default(ServiceClusterStatus::Draft)
                    ->required(),
                Section::make('SEO')
                    ->collapsible()
                    ->components([
                        TextInput::make('meta_title')
                            ->helperText('Deixe em branco para usar o título gerado automaticamente.'),
                        TextInput::make('meta_description')
                            ->helperText('Deixe em branco para usar a descrição gerada automaticamente.'),
                        TextInput::make('canonical'),
                        TextInput::make('robots')
                            ->required()
                            ->default('index,follow'),
                    ]),
                KeyValue::make('ai_brief')
                    ->label('Briefing usado pela IA')
                    ->disabled()
                    ->visible(fn (?ServiceCluster $record): bool => filled($record?->ai_brief))
                    ->columnSpanFull(),
                Textarea::make('ai_error')
                    ->label('Erro da geração')
                    ->disabled()
                    ->visible(fn (?ServiceCluster $record): bool => $record?->status === ServiceClusterStatus::Failed)
                    ->columnSpanFull(),
            ]);
    }
}
