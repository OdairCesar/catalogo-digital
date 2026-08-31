<?php

use App\Enums\AudienceKnowledgeLevel;
use App\Enums\BrandPresence;
use App\Enums\ContentGoal;
use App\Enums\ImageStyle;
use App\Enums\PostLength;
use App\Enums\PostStatus;
use App\Enums\WritingTone;
use App\Jobs\GenerateAiBlogPost;
use App\Models\Post;
use App\Services\Blog\PostAiBrief;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Images\CreateResponse as ImageCreateResponse;

function fakeBrief(): array
{
    return (new PostAiBrief(
        title: 'Como escolher um sistema para clínicas',
        topic: null,
        primaryKeyword: 'sistema para clínicas',
        secondaryKeywords: ['software médico'],
        targetAudience: 'donos de clínicas',
        knowledgeLevel: AudienceKnowledgeLevel::Intermediate,
        searchIntent: 'quanto custa um sistema para clínica',
        goal: ContentGoal::GenerateLeads,
        readerInterests: [],
        brandPresence: BrandPresence::Subtle,
        cityId: null,
        competitors: null,
        length: PostLength::Small,
        tone: WritingTone::Professional,
        structure: [],
        imageStyle: ImageStyle::Photorealistic,
    ))->toArray();
}

function fakeChatResponse(): CreateResponse
{
    return CreateResponse::fake([
        'model' => 'gpt-4.1',
        'choices' => [[
            'message' => [
                'content' => json_encode([
                    'title' => 'Como escolher um sistema para clínicas',
                    'excerpt' => 'Um resumo curto sobre sistemas para clínicas.',
                    'content_html' => '<h2>Introdução</h2><p>Texto de exemplo.</p>',
                    'meta_title' => 'Sistema para clínicas | OD Tec',
                    'meta_description' => 'Descubra como escolher o melhor sistema para sua clínica.',
                    'tags' => ['clinicas', 'sistemas', 'saude'],
                    'image_prompt' => 'A modern clinic reception with a tablet showing a scheduling app.',
                ]),
            ],
        ]],
    ]);
}

test('generating a post succeeds and stores the parsed ai response and cover image', function () {
    Storage::fake('cloudinary');

    OpenAI::fake([
        fakeChatResponse(),
        ImageCreateResponse::fake([
            'data' => [['b64_json' => base64_encode('fake-image-bytes')]],
        ]),
    ]);

    $post = Post::factory()->generating()->create(['ai_brief' => fakeBrief()]);

    Bus::dispatchSync(new GenerateAiBlogPost($post));

    $post->refresh();

    expect($post->status)->toBe(PostStatus::Draft)
        ->and($post->title)->toBe('Como escolher um sistema para clínicas')
        ->and($post->slug)->not->toBeNull()
        ->and($post->excerpt)->toBe('Um resumo curto sobre sistemas para clínicas.')
        ->and($post->content)->toContain('Texto de exemplo.')
        ->and($post->tags)->toBe(['clinicas', 'sistemas', 'saude'])
        ->and($post->ai_model)->toBe('gpt-4.1')
        ->and($post->ai_error)->toBeNull()
        ->and($post->cover_image)->not->toBeNull();

    Storage::disk('cloudinary')->assertExists($post->cover_image);
});

test('a cover image generation failure still leaves the post as a draft without a cover image', function () {
    Storage::fake('cloudinary');

    OpenAI::fake([
        fakeChatResponse(),
        new Exception('Unknown parameter: \'style\'.'),
    ]);

    $post = Post::factory()->generating()->create(['ai_brief' => fakeBrief()]);

    Bus::dispatchSync(new GenerateAiBlogPost($post));

    $post->refresh();

    expect($post->status)->toBe(PostStatus::Draft)
        ->and($post->ai_error)->toBeNull()
        ->and($post->cover_image)->toBeNull();
});

test('an invalid ai response marks the post as failed with an error message', function () {
    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [[
                'message' => [
                    'content' => json_encode(['title' => 'Só o título, faltando os outros campos']),
                ],
            ]],
        ]),
    ]);

    $post = Post::factory()->generating()->create(['ai_brief' => fakeBrief()]);

    Bus::dispatchSync(new GenerateAiBlogPost($post));

    $post->refresh();

    expect($post->status)->toBe(PostStatus::Failed)
        ->and($post->ai_error)->not->toBeNull();
});

test('the failed hook marks the post as failed with the exception message', function () {
    $post = Post::factory()->generating()->create(['ai_brief' => fakeBrief()]);

    (new GenerateAiBlogPost($post))->failed(new Exception('Falha de conexão com a OpenAI'));

    $post->refresh();

    expect($post->status)->toBe(PostStatus::Failed)
        ->and($post->ai_error)->toBe('Falha de conexão com a OpenAI');
});
