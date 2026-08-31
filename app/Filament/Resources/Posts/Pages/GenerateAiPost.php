<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Actions\Support\GenerateUniqueSlug;
use App\Enums\PostStatus;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Posts\Schemas\GenerateAiPostForm;
use App\Jobs\GenerateAiBlogPost;
use App\Models\AiBriefSuggestion;
use App\Models\Post;
use App\Services\Blog\PostAiBrief;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;

/**
 * @property-read Schema $form
 */
class GenerateAiPost extends Page
{
    protected static string $resource = PostResource::class;

    protected string $view = 'filament.resources.posts.pages.generate-ai-post';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Gerar post com IA';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return GenerateAiPostForm::configure($schema)->statePath('data');
    }

    public function generate(GenerateUniqueSlug $generateSlug): void
    {
        $brief = PostAiBrief::fromArray($this->form->getState());

        foreach ($brief->secondaryKeywords as $keyword) {
            AiBriefSuggestion::remember('secondary_keywords', $keyword);
        }

        $title = $brief->title ?? $brief->topic ?? 'Novo post';

        $post = Post::query()->create([
            'user_id' => auth()->id(),
            'title' => $title,
            'slug' => $generateSlug(Post::class, $title),
            'status' => PostStatus::Generating,
            'ai_brief' => $brief->toArray(),
        ]);

        GenerateAiBlogPost::dispatch($post);

        Notification::make()
            ->title('Geração iniciada')
            ->body('A IA está escrevendo o post. O rascunho aparecerá em instantes na tela de edição.')
            ->success()
            ->send();

        $this->redirect(PostResource::getUrl('edit', ['record' => $post]));
    }
}
