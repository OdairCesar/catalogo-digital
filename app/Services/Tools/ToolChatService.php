<?php

namespace App\Services\Tools;

use App\Enums\ToolSubmissionStatus;
use App\Models\ToolSubmission;
use App\Services\Ai\TextGenerator;
use App\Services\Ai\ValidatesJsonPayload;

final class ToolChatService
{
    use ValidatesJsonPayload;

    public function __construct(
        private readonly ToolTurnPromptBuilder $promptBuilder,
        private readonly TextGenerator $textGenerator,
    ) {}

    public function startInterview(ToolSubmission $submission, ToolDefinition $tool): void
    {
        $result = $this->requestTurn($submission, $tool);

        $submission->update([
            'messages' => [
                ['role' => 'assistant', 'content' => $result->reply],
            ],
        ]);
    }

    public function sendTurn(ToolSubmission $submission, ToolDefinition $tool, string $userMessage): void
    {
        $messages = $submission->messages;
        $messages[] = ['role' => 'user', 'content' => $userMessage];
        $submission->update(['messages' => $messages]);

        $result = $this->requestTurn($submission, $tool);

        $questionsAsked = $submission->questions_asked + 1;
        $readyForReport = $result->readyForReport || $questionsAsked >= $tool->maxFollowupQuestions;

        $messages[] = ['role' => 'assistant', 'content' => $result->reply];

        $submission->update([
            'messages' => $messages,
            'questions_asked' => $questionsAsked,
            'status' => $readyForReport ? ToolSubmissionStatus::GeneratingReport : ToolSubmissionStatus::InProgress,
        ]);
    }

    private function requestTurn(ToolSubmission $submission, ToolDefinition $tool): ToolTurnResult
    {
        $result = $this->textGenerator->generate(
            $this->promptBuilder->messagesFor($submission, $tool),
            $this->promptBuilder->responseFormat(),
            0.6,
        );

        return $this->parse($result->content);
    }

    private function parse(string $jsonPayload): ToolTurnResult
    {
        $data = $this->decodeJsonObject($jsonPayload);

        return new ToolTurnResult(
            reply: $this->requireString($data, 'reply'),
            readyForReport: (bool) ($data['ready_for_report'] ?? false),
        );
    }
}
