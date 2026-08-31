<?php

namespace App\Services\Ai;

use App\Exceptions\AiGenerationException;

/**
 * Shared narrowing helpers for validating a decoded AI JSON response body.
 * Used by the various *ContentParser/*ReportParser classes so each one only
 * has to define its own field shape, not how to safely require a string or
 * array field out of the decoded payload.
 */
trait ValidatesJsonPayload
{
    /**
     * @return array<array-key, mixed>
     */
    private function decodeJsonObject(string $jsonPayload): array
    {
        $data = json_decode($jsonPayload, associative: true);

        if (! is_array($data)) {
            throw AiGenerationException::invalidResponseShape();
        }

        return $data;
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    private function requireString(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        if (! is_string($value) || $value === '') {
            throw AiGenerationException::invalidResponseShape();
        }

        return $value;
    }

    private function requireStringValue(mixed $value): string
    {
        if (! is_string($value) || $value === '') {
            throw AiGenerationException::invalidResponseShape();
        }

        return $value;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    private function requireArray(array $data, string $key): array
    {
        $value = $data[$key] ?? null;

        if (! is_array($value)) {
            throw AiGenerationException::invalidResponseShape();
        }

        return $value;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<int, string>
     */
    private function requireStringArray(array $data, string $key): array
    {
        return array_values(array_map($this->requireStringValue(...), $this->requireArray($data, $key)));
    }
}
