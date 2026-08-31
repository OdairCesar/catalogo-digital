<?php

use App\Enums\AudienceKnowledgeLevel;
use App\Enums\BrandPresence;
use App\Enums\ContentGoal;
use App\Enums\PostLength;
use App\Enums\PostStatus;
use App\Enums\WritingTone;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Posts\Pages\GenerateAiPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Jobs\GenerateAiBlogPost;
use App\Models\AiBriefSuggestion;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the categories resource index page renders', function () {
    Category::factory()->count(2)->create();

    $this->get('/admin/categories')->assertOk();
});

test('the posts resource index page renders', function () {
    Post::factory()->count(2)->create();

    $this->get('/admin/posts')->assertOk();
});

test('the posts resource index page renders when a post has a cover image on the cloudinary disk', function () {
    Post::factory()->create(['cover_image' => 'posts/example-cover']);

    $this->get('/admin/posts')->assertOk();
});

test('the generate ai post page renders', function () {
    $this->get('/admin/posts/generate')->assertOk();
});

test('creating a category through the resource form works end to end', function () {
    Livewire::test(CreateCategory::class)
        ->fillForm([
            'name' => 'Tecnologia',
            'slug' => 'tecnologia',
            'description' => 'Artigos sobre tecnologia.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Category::where('slug', 'tecnologia')->exists())->toBeTrue();
});

test('submitting the ai briefing form creates a generating post and dispatches the job', function () {
    Queue::fake();

    Livewire::test(GenerateAiPost::class)
        ->fillForm([
            'topic' => 'Como escolher um sistema para clínicas',
            'knowledge_level' => AudienceKnowledgeLevel::Intermediate->value,
            'goal' => ContentGoal::GenerateLeads->value,
            'brand_presence' => BrandPresence::Subtle->value,
            'length' => PostLength::Small->value,
            'tone' => WritingTone::Professional->value,
        ])
        ->call('generate');

    $post = Post::sole();

    expect($post->status)->toBe(PostStatus::Generating)
        ->and($post->ai_brief['topic'])->toBe('Como escolher um sistema para clínicas');

    Queue::assertPushed(GenerateAiBlogPost::class, fn (GenerateAiBlogPost $job): bool => $job->post->is($post));
});

test('the ai briefing form accepts a preset audience suggestion', function () {
    Queue::fake();

    Livewire::test(GenerateAiPost::class)
        ->fillForm([
            'topic' => 'Como escolher um sistema para clínicas',
            'target_audience' => 'Médico',
            'knowledge_level' => AudienceKnowledgeLevel::Intermediate->value,
            'goal' => ContentGoal::GenerateLeads->value,
            'brand_presence' => BrandPresence::Subtle->value,
            'length' => PostLength::Small->value,
            'tone' => WritingTone::Professional->value,
        ])
        ->call('generate');

    expect(Post::sole()->ai_brief['target_audience'])->toBe('Médico');
});

test('submitting the ai briefing form remembers new secondary keywords as future suggestions', function () {
    Queue::fake();

    Livewire::test(GenerateAiPost::class)
        ->fillForm([
            'topic' => 'Como escolher um sistema para clínicas',
            'secondary_keywords' => ['software para clínicas', 'sistema de gestão médica'],
            'knowledge_level' => AudienceKnowledgeLevel::Intermediate->value,
            'goal' => ContentGoal::GenerateLeads->value,
            'brand_presence' => BrandPresence::Subtle->value,
            'length' => PostLength::Small->value,
            'tone' => WritingTone::Professional->value,
        ])
        ->call('generate');

    expect(AiBriefSuggestion::query()->forField('secondary_keywords')->orderBy('value')->pluck('value')->all())
        ->toBe(['sistema de gestão médica', 'software para clínicas']);
});

test('the posts list can be filtered by author', function () {
    $author = User::factory()->admin()->create();
    $otherAuthor = User::factory()->admin()->create();

    $ownPost = Post::factory()->create(['user_id' => $author->id]);
    $otherPost = Post::factory()->create(['user_id' => $otherAuthor->id]);

    Livewire::test(ListPosts::class)
        ->assertCanSeeTableRecords([$ownPost, $otherPost])
        ->filterTable('author', $author->id)
        ->assertCanSeeTableRecords([$ownPost])
        ->assertCanNotSeeTableRecords([$otherPost]);
});

test('the toggle publish status action is hidden for a post that is still generating', function () {
    $generating = Post::factory()->create(['status' => PostStatus::Generating]);
    $draft = Post::factory()->create(['status' => PostStatus::Draft]);

    Livewire::test(ListPosts::class)
        ->assertTableActionHidden('togglePublishStatus', $generating)
        ->assertTableActionVisible('togglePublishStatus', $draft);
});

test('the toggle publish status action publishes a draft post', function () {
    $post = Post::factory()->create(['status' => PostStatus::Draft]);

    Livewire::test(ListPosts::class)
        ->callTableAction('togglePublishStatus', $post);

    expect($post->refresh()->status)->toBe(PostStatus::Published);
});

test('the categories list can be filtered by whether they have posts', function () {
    $withPosts = Category::factory()->create();
    Post::factory()->create(['category_id' => $withPosts->id]);
    $withoutPosts = Category::factory()->create();

    Livewire::test(ListCategories::class)
        ->assertCanSeeTableRecords([$withPosts, $withoutPosts])
        ->filterTable('has_posts', true)
        ->assertCanSeeTableRecords([$withPosts])
        ->assertCanNotSeeTableRecords([$withoutPosts]);
});
