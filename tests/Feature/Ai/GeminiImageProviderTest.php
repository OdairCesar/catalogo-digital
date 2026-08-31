<?php

use App\Exceptions\AiGenerationException;
use App\Services\Ai\Gemini\GeminiImageProvider;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;

test('generateImageBytes decodes the inline image data returned by gemini', function () {
    Gemini::fake([
        GenerateContentResponse::from([
            'candidates' => [[
                'content' => [
                    'parts' => [[
                        'inlineData' => [
                            'mimeType' => 'image/png',
                            'data' => base64_encode('fake-image-bytes'),
                        ],
                    ]],
                    'role' => 'model',
                ],
            ]],
            'usageMetadata' => ['totalTokenCount' => 1],
        ]),
    ]);

    $bytes = app(GeminiImageProvider::class)->generateImageBytes('A modern office');

    expect($bytes)->toBe('fake-image-bytes');

    Gemini::generativeModel(model: config()->string('services.gemini.image_model'))->assertSent(
        fn (string $method, array $args): bool => $method === 'generateContent'
    );
});

test('generateImageBytes requests the configured aspect ratio', function () {
    config(['services.gemini.image_aspect_ratio' => '16:9']);

    Gemini::fake([
        GenerateContentResponse::from([
            'candidates' => [[
                'content' => [
                    'parts' => [[
                        'inlineData' => [
                            'mimeType' => 'image/png',
                            'data' => base64_encode('fake-image-bytes'),
                        ],
                    ]],
                    'role' => 'model',
                ],
            ]],
            'usageMetadata' => ['totalTokenCount' => 1],
        ]),
    ]);

    app(GeminiImageProvider::class)->generateImageBytes('A modern office');

    Gemini::generativeModel(model: config()->string('services.gemini.image_model'))->assertFunctionCalled(
        function (string $method, array $args): bool {
            return $method === 'withGenerationConfig'
                && $args[0]->imageConfig->aspectRatio === '16:9';
        }
    );
});

test('generateImageBytes throws when gemini returns no inline image data', function () {
    Gemini::fake([
        GenerateContentResponse::from([
            'candidates' => [[
                'content' => [
                    'parts' => [['text' => 'no image here']],
                    'role' => 'model',
                ],
            ]],
            'usageMetadata' => ['totalTokenCount' => 1],
        ]),
    ]);

    app(GeminiImageProvider::class)->generateImageBytes('A modern office');
})->throws(AiGenerationException::class);

test('generateImageBytes throws when the inline image data is an empty string', function () {
    Gemini::fake([
        GenerateContentResponse::from([
            'candidates' => [[
                'content' => [
                    'parts' => [[
                        'inlineData' => ['mimeType' => 'image/png', 'data' => ''],
                    ]],
                    'role' => 'model',
                ],
            ]],
            'usageMetadata' => ['totalTokenCount' => 1],
        ]),
    ]);

    app(GeminiImageProvider::class)->generateImageBytes('A modern office');
})->throws(AiGenerationException::class);
