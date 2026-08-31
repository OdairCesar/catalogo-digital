<?php

namespace App\Services\Ai;

/**
 * Shared narrowing helpers for walking a provider-neutral JSON schema array
 * (see JsonSchema). Used by provider-specific TextGenerator implementations
 * so each one only has to define how to transform a node, not how to safely
 * find its children.
 */
trait TraversesJsonSchema
{
    /**
     * @param  array<array-key, mixed>  $schema
     * @return array<string, array<array-key, mixed>>|null
     */
    private function schemaProperties(array $schema): ?array
    {
        if (! is_array($schema['properties'] ?? null)) {
            return null;
        }

        $properties = [];

        foreach ($schema['properties'] as $name => $property) {
            if (is_string($name) && is_array($property)) {
                $properties[$name] = $property;
            }
        }

        return $properties;
    }

    /**
     * @param  array<array-key, mixed>  $schema
     * @return array<array-key, mixed>|null
     */
    private function schemaItems(array $schema): ?array
    {
        return is_array($schema['items'] ?? null) ? $schema['items'] : null;
    }
}
