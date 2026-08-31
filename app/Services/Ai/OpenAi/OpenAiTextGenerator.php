<?php

namespace App\Services\Ai\OpenAi;

use App\Services\Ai\JsonSchema;
use App\Services\Ai\TextGenerationResult;
use App\Services\Ai\TextGenerator;
use App\Services\Ai\TraversesJsonSchema;
use OpenAI\Laravel\Facades\OpenAI;

final class OpenAiTextGenerator implements TextGenerator
{
    use TraversesJsonSchema;

    public function generate(array $messages, JsonSchema $schema, float $temperature): TextGenerationResult
    {
        $response = OpenAI::chat()->create([
            'model' => config('services.openai.model'),
            'messages' => $messages,
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => $schema->name,
                    'strict' => true,
                    'schema' => $this->withStrictConstraints($schema->schema),
                ],
            ],
            'temperature' => $temperature,
        ]);

        return new TextGenerationResult(
            content: $response->choices[0]->message->content ?? '',
            model: $response->model,
        );
    }

    /**
     * OpenAI's strict structured-output mode requires `additionalProperties: false`
     * on every object node in the schema, not just the root.
     *
     * @param  array<array-key, mixed>  $schema
     * @return array<array-key, mixed>
     */
    private function withStrictConstraints(array $schema): array
    {
        if (($schema['type'] ?? null) === 'object') {
            $schema['additionalProperties'] = false;

            if ($properties = $this->schemaProperties($schema)) {
                foreach ($properties as $name => $property) {
                    $properties[$name] = $this->withStrictConstraints($property);
                }

                $schema['properties'] = $properties;
            }
        }

        if ($items = $this->schemaItems($schema)) {
            $schema['items'] = $this->withStrictConstraints($items);
        }

        return $schema;
    }
}
