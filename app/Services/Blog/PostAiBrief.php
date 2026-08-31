<?php

namespace App\Services\Blog;

use App\Enums\AudienceKnowledgeLevel;
use App\Enums\BrandPresence;
use App\Enums\ContentGoal;
use App\Enums\ContentStructure;
use App\Enums\ImageStyle;
use App\Enums\PostLength;
use App\Enums\ReaderInterest;
use App\Enums\WritingTone;
use BackedEnum;

final readonly class PostAiBrief
{
    /**
     * @param  array<int, string>  $secondaryKeywords
     * @param  array<int, ReaderInterest>  $readerInterests
     * @param  array<int, ContentStructure>  $structure
     */
    public function __construct(
        public ?string $title,
        public ?string $topic,
        public ?string $primaryKeyword,
        public array $secondaryKeywords,
        public ?string $targetAudience,
        public AudienceKnowledgeLevel $knowledgeLevel,
        public ?string $searchIntent,
        public ContentGoal $goal,
        public array $readerInterests,
        public BrandPresence $brandPresence,
        public ?int $cityId,
        public ?string $competitors,
        public PostLength $length,
        public WritingTone $tone,
        public array $structure,
        public ImageStyle $imageStyle,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'topic' => $this->topic,
            'primary_keyword' => $this->primaryKeyword,
            'secondary_keywords' => $this->secondaryKeywords,
            'target_audience' => $this->targetAudience,
            'knowledge_level' => $this->knowledgeLevel->value,
            'search_intent' => $this->searchIntent,
            'goal' => $this->goal->value,
            'reader_interests' => array_map(fn (ReaderInterest $interest): string => $interest->value, $this->readerInterests),
            'brand_presence' => $this->brandPresence->value,
            'city_id' => $this->cityId,
            'competitors' => $this->competitors,
            'length' => $this->length->value,
            'tone' => $this->tone->value,
            'structure' => array_map(fn (ContentStructure $structure): string => $structure->value, $this->structure),
            'image_style' => $this->imageStyle->value,
        ];
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: self::stringOrNull($data['title'] ?? null),
            topic: self::stringOrNull($data['topic'] ?? null),
            primaryKeyword: self::stringOrNull($data['primary_keyword'] ?? null),
            secondaryKeywords: array_values(array_filter(array_map(
                fn (mixed $value): string => self::scalarToString($value),
                (array) ($data['secondary_keywords'] ?? []),
            ))),
            targetAudience: self::stringOrNull($data['target_audience'] ?? null),
            knowledgeLevel: self::enumFrom(AudienceKnowledgeLevel::class, $data['knowledge_level'] ?? null),
            searchIntent: self::stringOrNull($data['search_intent'] ?? null),
            goal: self::enumFrom(ContentGoal::class, $data['goal'] ?? null),
            readerInterests: array_map(
                fn (mixed $value): ReaderInterest => self::enumFrom(ReaderInterest::class, $value),
                array_values((array) ($data['reader_interests'] ?? [])),
            ),
            brandPresence: self::enumFrom(BrandPresence::class, $data['brand_presence'] ?? null),
            cityId: self::intOrNull($data['city_id'] ?? null),
            competitors: self::stringOrNull($data['competitors'] ?? null),
            length: self::enumFrom(PostLength::class, $data['length'] ?? null),
            tone: self::enumFrom(WritingTone::class, $data['tone'] ?? null),
            structure: array_map(
                fn (mixed $value): ContentStructure => self::enumFrom(ContentStructure::class, $value),
                array_values((array) ($data['structure'] ?? [])),
            ),
            imageStyle: self::enumFrom(ImageStyle::class, $data['image_style'] ?? null),
        );
    }

    private static function stringOrNull(mixed $value): ?string
    {
        return blank($value) ? null : self::scalarToString($value);
    }

    private static function intOrNull(mixed $value): ?int
    {
        return match (true) {
            blank($value) => null,
            is_int($value) => $value,
            is_string($value), is_float($value) => (int) $value,
            default => null,
        };
    }

    private static function scalarToString(mixed $value): string
    {
        return match (true) {
            is_string($value) => $value,
            is_int($value), is_float($value) => (string) $value,
            is_bool($value) => $value ? '1' : '',
            default => '',
        };
    }

    /**
     * @template T of BackedEnum
     *
     * @param  class-string<T>  $enum
     * @return T
     */
    private static function enumFrom(string $enum, mixed $value): BackedEnum
    {
        return $value instanceof $enum ? $value : $enum::from(self::scalarToString($value));
    }
}
