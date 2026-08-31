<?php

namespace App\Jobs;

use App\Enums\ServiceClusterStatus;
use App\Exceptions\AiGenerationException;
use App\Jobs\Concerns\HandlesAiGenerationFailure;
use App\Models\ServiceCluster;
use App\Services\Ai\TextGenerator;
use App\Services\ServiceCluster\ServiceClusterAiBrief;
use App\Services\ServiceCluster\ServiceClusterAiBriefPromptBuilder;
use App\Services\ServiceCluster\ServiceClusterAiContentParser;
use App\Services\ServiceCluster\ServiceClusterCoverImageGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateServiceClusterContent implements ShouldQueue
{
    use HandlesAiGenerationFailure, Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    public int $timeout = 300;

    public function __construct(public readonly ServiceCluster $cluster) {}

    public function handle(
        ServiceClusterAiBriefPromptBuilder $promptBuilder,
        ServiceClusterAiContentParser $parser,
        ServiceClusterCoverImageGenerator $coverImageGenerator,
        TextGenerator $textGenerator,
    ): void {
        $this->cluster->loadMissing('service');
        $brief = ServiceClusterAiBrief::fromArray($this->cluster->ai_brief ?? []);

        try {
            $messages = $promptBuilder->build($brief, $this->cluster->service);

            $result = $textGenerator->generate(
                [
                    ['role' => 'system', 'content' => $messages['system']],
                    ['role' => 'user', 'content' => $messages['user']],
                ],
                $promptBuilder->responseFormat(),
                0.7,
            );

            $generated = $parser->parse($result->content);
        } catch (AiGenerationException $exception) {
            $this->markFailed($exception);

            return;
        }

        $heroImage = $this->attemptOptionalStep(
            fn () => $coverImageGenerator->generate($generated->imagePrompt),
        );

        $this->cluster->update([
            'name' => $generated->title,
            'title' => $generated->title,
            'subtitle' => $generated->subtitle,
            'description' => $generated->description,
            'benefits' => $generated->benefits,
            'faq' => $generated->faq,
            'keywords' => $generated->keywords,
            'hero_image' => $heroImage,
            'meta_title' => $generated->metaTitle,
            'meta_description' => $generated->metaDescription,
            'ai_model' => $result->model,
            'status' => ServiceClusterStatus::Draft,
            'ai_error' => null,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $this->markFailed($exception);
    }

    private function markFailed(?Throwable $exception): void
    {
        $this->markModelFailed($this->cluster, ServiceClusterStatus::Failed, $exception);
    }
}
