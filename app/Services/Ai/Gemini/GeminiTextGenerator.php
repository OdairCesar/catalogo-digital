<?php

namespace App\Services\Ai\Gemini;

use App\Exceptions\AiGenerationException;
use App\Services\Ai\JsonSchema;
use App\Services\Ai\TextGenerationResult;
use App\Services\Ai\TextGenerator;
use App\Services\Ai\TraversesJsonSchema;
use Gemini\Data\Content;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\ResponseMimeType;
use Gemini\Enums\Role;
use Gemini\Laravel\Facades\Gemini;
use InvalidArgumentException;
use ValueError;

final class GeminiTextGenerator implements TextGenerator
{
    use TraversesJsonSchema;

    public function generate(array $messages, JsonSchema $schema, float $temperature): TextGenerationResult
    {
        [$systemInstruction, $conversation] = $this->splitSystemMessage($messages);

        $model = Gemini::generativeModel(model: config()->string('services.gemini.model'))
            ->withGenerationConfig(new GenerationConfig(
                temperature: $temperature,
                responseMimeType: ResponseMimeType::APPLICATION_JSON,
                responseSchema: $this->toGeminiSchema($schema->schema),
            ));

        if ($systemInstruction !== null) {
            $model = $model->withSystemInstruction(Content::parse($systemInstruction));
        }

        $contents = array_map(
            fn (array $message): Content => Content::parse(
                $message['content'],
                role: $message['role'] === 'assistant' ? Role::MODEL : Role::USER,
            ),
            $conversation,
        );

        $response = $model->generateContent(...$contents);

        try {
            $content = $response->text();
        } catch (ValueError) {
            throw AiGenerationException::invalidResponseShape();
        }

        return new TextGenerationResult(
            content: $content,
            model: $response->modelVersion ?? config()->string('services.gemini.model'),
        );
    }

    /**
     * Separates system-role messages (merged into a single instruction, since
     * Gemini has one systemInstruction slot) from the user/assistant turns.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{0: ?string, 1: array<int, array{role: string, content: string}>}
     */
    private function splitSystemMessage(array $messages): array
    {
        $systemParts = [];
        $conversation = [];

        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $systemParts[] = $message['content'];

                continue;
            }

            $conversation[] = $message;
        }

        return [$systemParts === [] ? null : implode("\n\n", $systemParts), $conversation];
    }

    /**
     * Converts the provider-neutral JSON schema array into Gemini's Schema object graph.
     *
     * @param  array<array-key, mixed>  $schema
     */
    private function toGeminiSchema(array $schema): Schema
    {
        $type = $schema['type'] ?? null;

        if (! is_string($type)) {
            throw new InvalidArgumentException('JSON schema node is missing a string "type".');
        }

        $properties = null;

        if ($schemaProperties = $this->schemaProperties($schema)) {
            $properties = array_map(
                fn (array $property): Schema => $this->toGeminiSchema($property),
                $schemaProperties,
            );
        }

        $items = $this->schemaItems($schema);
        $items = $items !== null ? $this->toGeminiSchema($items) : null;

        return new Schema(
            type: DataType::from(strtoupper($type)),
            enum: $this->stringList($schema['enum'] ?? null),
            properties: $properties,
            required: $this->stringList($schema['required'] ?? null),
            items: $items,
        );
    }

    /**
     * @return array<string>|null
     */
    private function stringList(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $strings = [];

        foreach ($value as $item) {
            if (is_string($item)) {
                $strings[] = $item;
            }
        }

        return $strings;
    }
}
