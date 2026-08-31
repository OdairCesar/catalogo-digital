<?php

namespace App\Jobs;

use App\Enums\ToolSubmissionStatus;
use App\Exceptions\AiGenerationException;
use App\Jobs\Concerns\HandlesAiGenerationFailure;
use App\Mail\NewToolSubmissionCompleted;
use App\Models\ToolSubmission;
use App\Services\Ai\TextGenerator;
use App\Services\Tools\ToolRegistry;
use App\Services\Tools\ToolReportPromptBuilder;
use App\Services\Tools\ToolReportValidator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class GenerateToolReport implements ShouldQueue
{
    use HandlesAiGenerationFailure, Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    public int $timeout = 300;

    public function __construct(public readonly ToolSubmission $submission) {}

    public function handle(
        ToolRegistry $registry,
        ToolReportPromptBuilder $promptBuilder,
        ToolReportValidator $validator,
        TextGenerator $textGenerator,
    ): void {
        $tool = $registry->findOrFail($this->submission->tool_slug);

        try {
            $messages = $promptBuilder->build($this->submission, $tool);

            $result = $textGenerator->generate(
                [
                    ['role' => 'system', 'content' => $messages['system']],
                    ['role' => 'user', 'content' => $messages['user']],
                ],
                $tool->reportResponseFormat,
                0.5,
            );

            $report = $validator->validate($result->content, $tool->reportResponseFormat);
        } catch (AiGenerationException $exception) {
            $this->markFailed($exception);

            return;
        }

        $this->submission->update([
            'result' => $report,
            'ai_model' => $result->model,
            'status' => ToolSubmissionStatus::Completed,
            'ai_error' => null,
        ]);

        try {
            Mail::to(config('services.lead_notifications.email'))
                ->send(new NewToolSubmissionCompleted($this->submission, $tool->title));
        } catch (Throwable $exception) {
            Log::error('Falha ao enviar e-mail de notificação de submissão de ferramenta.', [
                'tool_submission_id' => $this->submission->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->markFailed($exception);
    }

    private function markFailed(?Throwable $exception): void
    {
        $this->markModelFailed($this->submission, ToolSubmissionStatus::Failed, $exception);
    }
}
