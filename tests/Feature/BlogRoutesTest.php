<?php

use App\Models\Category;
use App\Models\Post;

test('blog index page resolves', function () {
    Post::factory()->published()->count(2)->create();

    $this->get(route('blog.index'))->assertOk();
});

test('published post show page resolves', function () {
    $post = Post::factory()->published()->create();

    $this->get(route('blog.show', $post))->assertOk();
});

test('a draft post 404s on the public show page', function () {
    $post = Post::factory()->draft()->create();

    $this->get(route('blog.show', $post))->assertNotFound();
});

test('a post still being generated has no slug and is excluded from the published scope', function () {
    $post = Post::factory()->generating()->create();

    expect($post->slug)->toBeNull()
        ->and(Post::published()->whereKey($post->id)->exists())->toBeFalse();
});

test('category archive page resolves and lists only its published posts', function () {
    $category = Category::factory()->create();
    $matching = Post::factory()->published()->create(['category_id' => $category->id]);
    $other = Post::factory()->published()->create();

    $response = $this->get(route('blog.category', $category))->assertOk();

    $response->assertSee($matching->title)
        ->assertDontSee($other->title);
});

test('blog index only lists published posts', function () {
    $published = Post::factory()->published()->create();
    $draft = Post::factory()->draft()->create();

    $response = $this->get(route('blog.index'))->assertOk();

    $response->assertSee($published->title)
        ->assertDontSee($draft->title);
});
