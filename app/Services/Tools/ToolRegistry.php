<?php

namespace App\Services\Tools;

use RuntimeException;

final class ToolRegistry
{
    /**
     * @return list<ToolDefinition>
     */
    public function all(): array
    {
        $definitions = [];

        foreach (config()->array('tools') as $slug => $config) {
            if (! is_string($slug) || ! is_array($config)) {
                continue;
            }

            $definitions[] = ToolDefinition::fromConfig($slug, $config);
        }

        return $definitions;
    }

    public function find(string $slug): ?ToolDefinition
    {
        $config = config("tools.{$slug}");

        return is_array($config) ? ToolDefinition::fromConfig($slug, $config) : null;
    }

    public function findOrFail(string $slug): ToolDefinition
    {
        return $this->find($slug) ?? throw new RuntimeException("Ferramenta [{$slug}] não encontrada.");
    }
}
