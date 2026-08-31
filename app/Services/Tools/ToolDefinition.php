<?php

namespace App\Services\Tools;

use App\Services\Ai\JsonSchema;

final readonly class ToolDefinition
{
    public function __construct(
        public string $slug,
        public string $title,
        public string $tagline,
        public string $description,
        public string $icon,
        public int $maxFollowupQuestions,
        public string $interviewSystemPrompt,
        public string $reportSystemPrompt,
        public JsonSchema $reportResponseFormat,
        public string $resultView,
        /** @var array<string, string> */
        public array $resultLabels,
        public ?string $landingView,
    ) {}

    /**
     * @param  array<array-key, mixed>  $config
     */
    public static function fromConfig(string $slug, array $config): self
    {
        return new self(
            slug: $slug,
            title: self::stringValue($config, 'title'),
            tagline: self::stringValue($config, 'tagline'),
            description: self::stringValue($config, 'description'),
            icon: self::stringValue($config, 'icon'),
            maxFollowupQuestions: self::intValue($config, 'max_followup_questions'),
            interviewSystemPrompt: self::stringValue($config, 'interview_system_prompt'),
            reportSystemPrompt: self::stringValue($config, 'report_system_prompt'),
            reportResponseFormat: new JsonSchema(
                name: "{$slug}_report",
                schema: self::stringKeyedArray($config['report_response_format'] ?? null),
            ),
            resultView: self::stringValue($config, 'result_view'),
            resultLabels: self::stringMap($config['result_labels'] ?? null),
            landingView: is_string($config['landing_view'] ?? null) ? $config['landing_view'] : null,
        );
    }

    /**
     * @param  array<array-key, mixed>  $config
     */
    private static function stringValue(array $config, string $key): string
    {
        $value = $config[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    /**
     * @param  array<array-key, mixed>  $config
     */
    private static function intValue(array $config, string $key): int
    {
        $value = $config[$key] ?? null;

        return is_int($value) ? $value : 0;
    }

    /**
     * @return array<string, mixed>
     */
    private static function stringKeyedArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $result = [];

        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $result[$key] = $item;
            }
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    private static function stringMap(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $result = [];

        foreach ($value as $key => $item) {
            if (is_string($key) && is_string($item)) {
                $result[$key] = $item;
            }
        }

        return $result;
    }
}
