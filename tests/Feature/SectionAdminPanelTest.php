<?php

use App\Enums\SectionType;
use App\Filament\Resources\FaqGroups\Pages\CreateFaqGroup;
use App\Filament\Resources\FaqGroups\Pages\ListFaqGroups;
use App\Filament\Resources\InstagramPosts\Pages\CreateInstagramPost;
use App\Filament\Resources\InstagramPosts\Pages\ListInstagramPosts;
use App\Filament\Resources\PageBlocks\PageBlockResource;
use App\Filament\Resources\PageBlocks\Pages\EditPageBlock;
use App\Filament\Resources\PageBlocks\Pages\ListPageBlocks;
use App\Filament\Resources\Testimonials\Pages\CreateTestimonial;
use App\Filament\Resources\Testimonials\Pages\ListTestimonials;
use App\Models\Product;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the testimonials resource index page renders and only lists testimonials', function () {
    Section::factory()->type(SectionType::Testimonial)->create();
    Section::factory()->type(SectionType::FaqGroup)->create();

    Livewire::test(ListTestimonials::class)
        ->assertOk()
        ->assertCanSeeTableRecords(Section::query()->ofType(SectionType::Testimonial)->get())
        ->assertCanNotSeeTableRecords(Section::query()->ofType(SectionType::FaqGroup)->get());
});

test('creating a testimonial through the resource form sets its type automatically', function () {
    Livewire::test(CreateTestimonial::class)
        ->fillForm([
            'content' => 'Achei incrível.',
            'data.author_name' => 'Fulana',
            'status' => 'published',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $testimonial = Section::query()->ofType(SectionType::Testimonial)->first();

    expect($testimonial)->not->toBeNull()
        ->and($testimonial->type)->toBe(SectionType::Testimonial)
        ->and($testimonial->data['author_name'])->toBe('Fulana');
});

test('the testimonial form renders a none option for the product select', function () {
    Livewire::test(CreateTestimonial::class)
        ->assertOk()
        ->assertSee('Nenhum — avaliação da loja');
});

test('creating a testimonial linked to a product sets its product_id', function () {
    $product = Product::factory()->create();

    Livewire::test(CreateTestimonial::class)
        ->fillForm([
            'content' => 'Amei essa peça.',
            'data.author_name' => 'Fulana',
            'product_id' => $product->id,
            'status' => 'published',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $testimonial = Section::query()->ofType(SectionType::Testimonial)->firstOrFail();

    expect($testimonial->product_id)->toBe($product->id);
});

test('creating a faq group with nested questions works end to end', function () {
    Livewire::test(CreateFaqGroup::class)
        ->fillForm([
            'title' => 'Novo grupo',
            'data.faq' => [
                ['question' => 'Pergunta 1?', 'answer' => 'Resposta 1.'],
            ],
            'status' => 'published',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $group = Section::query()->ofType(SectionType::FaqGroup)->first();

    expect($group)->not->toBeNull()
        ->and($group->data['faq'])->toHaveCount(1)
        ->and($group->data['faq'][0]['question'])->toBe('Pergunta 1?');
});

test('a testimonial created through the admin is appended after existing testimonials, not defaulted to the top', function () {
    Section::factory()->type(SectionType::Testimonial)->create(['sort_order' => 0]);
    Section::factory()->type(SectionType::Testimonial)->create(['sort_order' => 1]);

    Livewire::test(CreateTestimonial::class)
        ->fillForm(['content' => 'Novo depoimento.', 'data.author_name' => 'Nova Pessoa'])
        ->call('create')
        ->assertHasNoFormErrors();

    $newTestimonial = Section::query()->ofType(SectionType::Testimonial)->where('content', 'Novo depoimento.')->firstOrFail();

    expect($newTestimonial->sort_order)->toBe(2);
});

test('the faq groups resource index page only lists faq groups', function () {
    Section::factory()->type(SectionType::FaqGroup)->create();
    Section::factory()->type(SectionType::Testimonial)->create();

    Livewire::test(ListFaqGroups::class)
        ->assertOk()
        ->assertCanSeeTableRecords(Section::query()->ofType(SectionType::FaqGroup)->get())
        ->assertCanNotSeeTableRecords(Section::query()->ofType(SectionType::Testimonial)->get());
});

test('the page blocks resource lists singleton block types except instagram and has no create action', function () {
    foreach (SectionType::singletons() as $type) {
        Section::factory()->type($type)->create();
    }

    Section::factory()->type(SectionType::Testimonial)->create();

    Livewire::test(ListPageBlocks::class)
        ->assertOk()
        ->assertCanSeeTableRecords(Section::query()->whereIn('type', SectionType::singletons())->where('type', '!=', SectionType::Instagram)->get())
        ->assertCanNotSeeTableRecords(Section::query()->ofType(SectionType::Testimonial)->get())
        ->assertCanNotSeeTableRecords(Section::query()->ofType(SectionType::Instagram)->get());

    expect(PageBlockResource::canCreate())->toBeFalse();
});

test('editing a page block updates the cached value returned by Section::block', function () {
    $hero = Section::factory()->type(SectionType::HomeHero)->create(['title' => 'Título antigo']);

    expect(Section::block(SectionType::HomeHero)->title)->toBe('Título antigo');

    Livewire::test(EditPageBlock::class, ['record' => $hero->getKey()])
        ->fillForm(['title' => 'Título novo'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Section::block(SectionType::HomeHero)->title)->toBe('Título novo');
});

test('editing the about page block through the resource form updates its rich data', function () {
    $about = Section::factory()->type(SectionType::About)->create(['title' => 'Título antigo']);

    Livewire::test(EditPageBlock::class, ['record' => $about->getKey()])
        ->fillForm([
            'title' => 'Novo título',
            'data.intro_paragraphs' => [['paragraph' => 'Parágrafo 1.'], ['paragraph' => 'Parágrafo 2.']],
            'data.missao_text' => 'Nova missão.',
            'data.quem_escolhe_text' => 'Novo texto.',
            'data.values' => [['title' => 'Valor 1', 'desc' => 'Descrição 1']],
            'data.manifesto_paragraphs' => [['paragraph' => 'Manifesto 1.']],
            'data.manifesto_tagline' => 'Nova tagline.',
            'data.cta_title' => 'Novo CTA',
            'data.cta_button_label' => 'Clique aqui',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $about->refresh();

    expect($about->title)->toBe('Novo título')
        ->and($about->data['intro_paragraphs'])->toBe([['paragraph' => 'Parágrafo 1.'], ['paragraph' => 'Parágrafo 2.']])
        ->and($about->data['values'][0]['title'])->toBe('Valor 1')
        ->and($about->data['manifesto_tagline'])->toBe('Nova tagline.');
});

test('the instagram posts resource index page only lists instagram posts', function () {
    Section::factory()->type(SectionType::InstagramPost)->create();
    Section::factory()->type(SectionType::Testimonial)->create();

    Livewire::test(ListInstagramPosts::class)
        ->assertOk()
        ->assertCanSeeTableRecords(Section::query()->ofType(SectionType::InstagramPost)->get())
        ->assertCanNotSeeTableRecords(Section::query()->ofType(SectionType::Testimonial)->get());
});

test('creating an instagram post through the resource form sets its type automatically', function () {
    Storage::fake('cloudinary');

    Livewire::test(CreateInstagramPost::class)
        ->fillForm([
            'image' => UploadedFile::fake()->image('post.jpg'),
            'data.link' => 'https://www.instagram.com/p/abc123/',
            'content' => 'Legenda de teste.',
            'status' => 'published',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Section::query()->ofType(SectionType::InstagramPost)->first();

    expect($post)->not->toBeNull()
        ->and($post->type)->toBe(SectionType::InstagramPost)
        ->and($post->data['link'])->toBe('https://www.instagram.com/p/abc123/');
});

test('editing the instagram intro text through the header action updates the cached block', function () {
    Section::factory()->type(SectionType::Instagram)->create(['title' => 'Título antigo', 'content' => 'Descrição antiga.']);

    Livewire::test(ListInstagramPosts::class)
        ->callAction('editIntroText', data: [
            'title' => 'Título novo',
            'content' => 'Descrição nova.',
        ])
        ->assertHasNoFormErrors();

    expect(Section::block(SectionType::Instagram)->title)->toBe('Título novo')
        ->and(Section::block(SectionType::Instagram)->content)->toBe('Descrição nova.');
});
