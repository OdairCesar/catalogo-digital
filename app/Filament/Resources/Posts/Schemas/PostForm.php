<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PostStatus;
use App\Filament\Support\Forms\AutoSlug;
use App\Filament\Support\Forms\CloudinaryImageUpload;
use App\Models\Post;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                AutoSlug::attach(
                    TextInput::make('title')
                        ->required()
                        ->columnSpanFull(),
                ),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Usado na URL, ex: /blog/{slug}.')
                    ->columnSpanFull(),
                Textarea::make('excerpt')
                    ->label('Resumo')
                    ->required()
                    ->helperText('Usado nos cards de listagem e como descrição de fallback para SEO.')
                    ->columnSpanFull(),
                RichEditor::make('content')
                    ->label('Conteúdo')
                    ->required()
                    ->columnSpanFull(),
                Select::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('user_id')
                    ->label('Autor')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->default(fn (): ?int => auth()->id() !== null ? (int) auth()->id() : null),
                TagsInput::make('tags')
                    ->label('Tags'),
                CloudinaryImageUpload::make('cover_image')
                    ->label('Imagem de capa'),
                Select::make('status')
                    ->options(PostStatus::class)
                    ->default(PostStatus::Draft)
                    ->required(),
                DateTimePicker::make('published_at')
                    ->label('Publicado em'),
                Section::make('SEO')
                    ->collapsible()
                    ->components([
                        TextInput::make('meta_title')
                            ->helperText('Deixe em branco para usar o título do post.'),
                        TextInput::make('meta_description')
                            ->helperText('Deixe em branco para usar o resumo do post.'),
                        TextInput::make('canonical'),
                        TextInput::make('robots')
                            ->required()
                            ->default('index,follow'),
                    ]),
                KeyValue::make('ai_brief')
                    ->label('Briefing usado pela IA')
                    ->disabled()
                    ->visible(fn (?Post $record): bool => filled($record?->ai_brief))
                    ->columnSpanFull(),
                Textarea::make('ai_error')
                    ->label('Erro da geração')
                    ->disabled()
                    ->visible(fn (?Post $record): bool => $record?->status === PostStatus::Failed)
                    ->columnSpanFull(),
            ]);
    }
}
