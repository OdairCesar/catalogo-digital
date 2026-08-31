<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\AudienceKnowledgeLevel;
use App\Enums\BrandPresence;
use App\Enums\ContentGoal;
use App\Enums\ContentStructure;
use App\Enums\ImageStyle;
use App\Enums\PostLength;
use App\Enums\ReaderInterest;
use App\Enums\WritingTone;
use App\Models\AiBriefSuggestion;
use App\Models\City;
use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class GenerateAiPostForm
{
    /** @var array<int, string> */
    private const AUDIENCE_SUGGESTIONS = [
        'Dono de restaurante',
        'Gerente de indústria',
        'Contador',
        'Médico',
        'Advogado',
        'Empreendedor',
        'Desenvolvedor',
    ];

    /** @var array<int, string> */
    private const SEARCH_INTENT_SUGGESTIONS = [
        'Quanto custa um sistema ERP',
        'Como criar um aplicativo',
        'Vale a pena fazer um site',
    ];

    /** @var array<int, string> */
    private const KEYWORD_SUGGESTIONS = [
        'Software para clínicas',
        'Sistema de gestão para empresas',
        'Criação de sites',
        'Criação de aplicativos',
        'Automação de processos',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Título (opcional)')
                    ->columnSpanFull(),
                TextInput::make('topic')
                    ->label('Assunto/tema')
                    ->required(fn (Get $get): bool => blank($get('title')))
                    ->helperText('Obrigatório se não houver título.')
                    ->columnSpanFull(),
                self::suggestableSelect('primary_keyword', 'Palavra-chave principal', self::KEYWORD_SUGGESTIONS),
                TagsInput::make('secondary_keywords')
                    ->label('Palavras-chave secundárias')
                    ->suggestions(fn (): array => self::storedSuggestions('secondary_keywords', [])),
                self::suggestableSelect('target_audience', 'Público-alvo', self::AUDIENCE_SUGGESTIONS)
                    ->helperText('Selecione um público comum ou adicione um novo.'),
                Select::make('knowledge_level')
                    ->label('Nível de conhecimento do público')
                    ->options(AudienceKnowledgeLevel::class)
                    ->default(AudienceKnowledgeLevel::Intermediate)
                    ->required(),
                self::suggestableSelect('search_intent', 'Intenção de busca', self::SEARCH_INTENT_SUGGESTIONS)
                    ->helperText('O que o leitor provavelmente pesquisou? Selecione um exemplo ou adicione um novo.')
                    ->columnSpanFull(),
                Select::make('goal')
                    ->label('Objetivo')
                    ->options(ContentGoal::class)
                    ->required(),
                CheckboxList::make('reader_interests')
                    ->label('O que o leitor quer descobrir')
                    ->options(ReaderInterest::class)
                    ->columns(2)
                    ->columnSpanFull(),
                Select::make('brand_presence')
                    ->label('Como a Fit By Cae aparece no texto')
                    ->options(BrandPresence::class)
                    ->default(BrandPresence::Subtle)
                    ->required(),
                Select::make('city_id')
                    ->label('Localização')
                    ->options(fn (): array => City::query()->active()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->helperText('Deixe em branco para um artigo genérico, sem foco em uma cidade.'),
                Textarea::make('competitors')
                    ->label('Concorrentes/referências')
                    ->helperText('Links ou descrição do que você quer superar em qualidade.')
                    ->columnSpanFull(),
                Select::make('length')
                    ->label('Tamanho')
                    ->options(PostLength::class)
                    ->default(PostLength::Medium)
                    ->required(),
                Select::make('tone')
                    ->label('Tom de escrita')
                    ->options(WritingTone::class)
                    ->default(WritingTone::Professional)
                    ->required(),
                Select::make('image_style')
                    ->label('Estilo da imagem de capa')
                    ->options(ImageStyle::class)
                    ->default(ImageStyle::Photorealistic)
                    ->required(),
                CheckboxList::make('structure')
                    ->label('Estrutura desejada')
                    ->options(ContentStructure::class)
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * A Select seeded with common example values (from the briefing spec) plus any value
     * previously entered by an admin, with a "create new" modal for anything not listed yet.
     *
     * @param  array<int, string>  $seed
     */
    private static function suggestableSelect(string $field, string $label, array $seed): Select
    {
        return Select::make($field)
            ->label($label)
            ->options(fn (): array => self::storedSuggestions($field, $seed))
            ->searchable()
            ->native(false)
            ->createOptionForm([
                TextInput::make('value')
                    ->label($label)
                    ->required(),
            ])
            ->createOptionUsing(self::createSuggestionUsing($field));
    }

    /**
     * @param  array<int, string>  $seed
     * @return array<string, string>
     */
    private static function storedSuggestions(string $field, array $seed): array
    {
        $stored = AiBriefSuggestion::query()
            ->forField($field)
            ->orderBy('value')
            ->get()
            ->map(fn (AiBriefSuggestion $suggestion): string => $suggestion->value)
            ->all();

        $options = array_unique([...$seed, ...$stored]);

        return array_combine($options, $options);
    }

    private static function createSuggestionUsing(string $field): Closure
    {
        return function (array $data) use ($field): string {
            $value = $data['value'] ?? null;
            $value = is_string($value) ? trim($value) : '';

            AiBriefSuggestion::remember($field, $value);

            return $value;
        };
    }
}
