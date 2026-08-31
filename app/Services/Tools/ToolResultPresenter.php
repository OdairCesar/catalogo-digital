<?php

namespace App\Services\Tools;

final class ToolResultPresenter
{
    /**
     * @param  array<string, mixed>  $result
     * @return list<array{label: string, value: mixed}>
     */
    public function rows(ToolDefinition $tool, array $result): array
    {
        $rows = [];

        foreach ($result as $key => $value) {
            $rows[] = [
                'label' => $tool->resultLabels[$key] ?? ucfirst(str_replace('_', ' ', $key)),
                'value' => $value,
            ];
        }

        return $rows;
    }
}
