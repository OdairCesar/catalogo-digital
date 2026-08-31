<?php

namespace App\Services\Tools;

use App\Exceptions\AiGenerationException;
use App\Services\Ai\JsonSchema;
use App\Services\Ai\TraversesJsonSchema;
use App\Services\Ai\ValidatesJsonPayload;

/**
 * Validates an AI-generated JSON payload against a tool's own JsonSchema,
 * returning a plain array. This is what lets every tool have a different
 * result shape without needing a dedicated Parser/DTO class each.
 */
final class ToolReportValidator
{
    use TraversesJsonSchema, ValidatesJsonPayload;

    /**
     * @return array<string, mixed>
     */
    public function validate(string $jsonPayload, JsonSchema $schema): array
    {
        return $this->validateObject($this->decodeJsonObject($jsonPayload), $schema->schema);
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @param  array<array-key, mixed>  $schema
     * @return array<string, mixed>
     */
    private function validateObject(array $data, array $schema): array
    {
        $properties = $this->schemaProperties($schema) ?? [];
        $required = is_array($schema['required'] ?? null) ? $schema['required'] : [];

        foreach ($required as $key) {
            if (! is_string($key) || ! array_key_exists($key, $data)) {
                throw AiGenerationException::invalidResponseShape();
            }
        }

        $result = [];

        foreach ($properties as $key => $propertySchema) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $result[$key] = $this->validateValue($data[$key], $propertySchema);
        }

        return $result;
    }

    /**
     * @param  array<array-key, mixed>  $schema
     */
    private function validateValue(mixed $value, array $schema): mixed
    {
        return match ($schema['type'] ?? null) {
            'string' => $this->requireStringValue($value),
            'number' => is_numeric($value) ? (float) $value : throw AiGenerationException::invalidResponseShape(),
            'boolean' => is_bool($value) ? $value : throw AiGenerationException::invalidResponseShape(),
            'array' => $this->validateArray($value, $this->schemaItems($schema) ?? []),
            'object' => is_array($value) ? $this->validateObject($value, $schema) : throw AiGenerationException::invalidResponseShape(),
            default => throw AiGenerationException::invalidResponseShape(),
        };
    }

    /**
     * @param  array<array-key, mixed>  $itemSchema
     * @return array<int, mixed>
     */
    private function validateArray(mixed $value, array $itemSchema): array
    {
        if (! is_array($value)) {
            throw AiGenerationException::invalidResponseShape();
        }

        return array_values(array_map(fn (mixed $item): mixed => $this->validateValue($item, $itemSchema), $value));
    }
}
