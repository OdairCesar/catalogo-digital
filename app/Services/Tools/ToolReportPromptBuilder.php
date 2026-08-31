<?php

namespace App\Services\Tools;

use App\Models\ToolSubmission;

final class ToolReportPromptBuilder
{
    /**
     * @return array{system: string, user: string}
     */
    public function build(ToolSubmission $submission, ToolDefinition $tool): array
    {
        return [
            'system' => $tool->reportSystemPrompt,
            'user' => $this->transcript($submission),
        ];
    }

    private function transcript(ToolSubmission $submission): string
    {
        $lines = ['Transcrição da conversa:'];

        foreach ($submission->messages as $turn) {
            $speaker = $turn['role'] === 'assistant' ? 'Consultor' : 'Visitante';
            $lines[] = "{$speaker}: {$turn['content']}";
        }

        return implode("\n", $lines);
    }
}
