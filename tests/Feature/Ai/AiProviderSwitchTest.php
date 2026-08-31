<?php

use App\Services\Ai\Gemini\GeminiImageProvider;
use App\Services\Ai\Gemini\GeminiTextGenerator;
use App\Services\Ai\ImageProvider;
use App\Services\Ai\OpenAi\OpenAiImageProvider;
use App\Services\Ai\OpenAi\OpenAiTextGenerator;
use App\Services\Ai\TextGenerator;

test('the openai implementations are resolved by default', function () {
    expect(app(TextGenerator::class))->toBeInstanceOf(OpenAiTextGenerator::class)
        ->and(app(ImageProvider::class))->toBeInstanceOf(OpenAiImageProvider::class);
});

test('services.ai.text_provider set to gemini resolves the gemini text generator', function () {
    config(['services.ai.text_provider' => 'gemini']);

    expect(app(TextGenerator::class))->toBeInstanceOf(GeminiTextGenerator::class);
});

test('services.ai.image_provider set to gemini resolves the gemini image provider', function () {
    config(['services.ai.image_provider' => 'gemini']);

    expect(app(ImageProvider::class))->toBeInstanceOf(GeminiImageProvider::class);
});

test('an unrecognized services.ai.text_provider value throws instead of silently falling back', function () {
    config(['services.ai.text_provider' => 'gemeni']);

    app(TextGenerator::class);
})->throws(InvalidArgumentException::class);

test('an unrecognized services.ai.image_provider value throws instead of silently falling back', function () {
    config(['services.ai.image_provider' => 'gemeni']);

    app(ImageProvider::class);
})->throws(InvalidArgumentException::class);
