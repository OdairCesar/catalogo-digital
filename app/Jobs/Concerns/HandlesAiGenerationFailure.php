<?php

namespace App\Jobs\Concerns;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;

/**
 * Shared failure-handling for the AI generation jobs: marking the target
 * model as failed with a truncated error message, and running an optional
 * step (e.g. cover image generation) that shouldn't abort the job if it fails.
 */
trait HandlesAiGenerationFailure
{
    private function markModelFailed(Model $model, BackedEnum $failedStatus, ?Throwable $exception): void
    {
        $model->update([
            'status' => $failedStatus,
            'ai_error' => $exception ? Str::limit($exception->getMessage(), 2000) : null,
        ]);
    }

    private function attemptOptionalStep(callable $step): mixed
    {
        try {
            return $step();
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }
}
