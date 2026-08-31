<?php

namespace App\Services\Tools;

use App\Models\ToolSubmission;
use App\Services\Ai\JsonSchema;

final class ToolTurnPromptBuilder
{
    /**
     * @return array<int, array{role: string, content: string}>
     */
    public function messagesFor(ToolSubmission $submission, ToolDefinition $tool): array
    {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt($submission, $tool)],
        ];

        if ($submission->messages === []) {
            $messages[] = ['role' => 'user', 'content' => 'Vamos começar a conversa. Faça a primeira pergunta.'];
        }

        foreach ($submission->messages as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
        }

        return $messages;
    }

    public function responseFormat(): JsonSchema
    {
        return new JsonSchema(
            name: 'tool_turn',
            schema: [
                'type' => 'object',
                'required' => ['reply', 'ready_for_report'],
                'properties' => [
                    'reply' => ['type' => 'string'],
                    'ready_for_report' => ['type' => 'boolean'],
                ],
            ],
        );
    }

    private function systemPrompt(ToolSubmission $submission, ToolDefinition $tool): string
    {
        return $tool->interviewSystemPrompt
            ."\n\nVocê já fez {$submission->questions_asked} de no máximo {$tool->maxFollowupQuestions} perguntas nesta conversa. Se estiver perto do limite, encaminhe a conversa para o encerramento.";
    }
}
