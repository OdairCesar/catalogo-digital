<?php

namespace App\Jobs;

use App\Actions\Support\GenerateUniqueSlug;
use App\Enums\PostStatus;
use App\Exceptions\AiGenerationException;
use App\Jobs\Concerns\HandlesAiGenerationFailure;
use App\Models\City;
use App\Models\Post;
use App\Services\Ai\TextGenerator;
use App\Services\Blog\PostAiBrief;
use App\Services\Blog\PostAiBriefPromptBuilder;
use App\Services\Blog\PostAiContentParser;
use App\Services\Blog\PostCoverImageGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateAiBlogPost implements ShouldQueue
{
    use HandlesAiGenerationFailure, Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60];

    public int $timeout = 300;

    public function __construct(public readonly Post $post) {}

    public function handle(
        PostAiBriefPromptBuilder $promptBuilder,
        PostAiContentParser $parser,
        GenerateUniqueSlug $generateSlug,
        PostCoverImageGenerator $coverImageGenerator,
        TextGenerator $textGenerator,
    ): void {
        $brief = PostAiBrief::fromArray($this->post->ai_brief ?? []);
        $city = $brief->cityId ? City::query()->find($brief->cityId) : null;

        try {
            $messages = $promptBuilder->build($brief, $city);

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

        $coverImage = $this->attemptOptionalStep(
            fn () => $coverImageGenerator->generate($generated->imagePrompt, $brief->imageStyle),
        );

        $title = $this->post->title ?: $generated->title;

        $this->post->update([
            'title' => $title,
            'slug' => $generateSlug(Post::class, $title, ignoreId: $this->post->id),
            'excerpt' => $generated->excerpt,
            'content' => $generated->contentHtml,
            'cover_image' => $coverImage,
            'tags' => $generated->tags,
            'meta_title' => $generated->metaTitle,
            'meta_description' => $generated->metaDescription,
            'ai_model' => $result->model,
            'status' => PostStatus::Draft,
            'ai_error' => null,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $this->markFailed($exception);
    }

    private function markFailed(?Throwable $exception): void
    {
        $this->markModelFailed($this->post, PostStatus::Failed, $exception);
    }
}
