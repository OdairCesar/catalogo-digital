<?php

use App\Exceptions\AiGenerationException;
use App\Services\Ai\AiImageGenerator;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Images\CreateResponse as ImageCreateResponse;

test('generate stores the decoded image on the cloudinary disk with the given filename suffix', function () {
    Storage::fake('cloudinary');

    OpenAI::fake([
        ImageCreateResponse::fake([
            'data' => [['b64_json' => base64_encode('fake-image-bytes')]],
        ]),
    ]);

    $path = app(AiImageGenerator::class)->generate('A modern office', 'hero');

    expect($path)->toEndWith('-hero');

    Storage::disk('cloudinary')->assertExists($path);
    expect(Storage::disk('cloudinary')->get($path))->toBe('fake-image-bytes');
});

test('generate throws when the ai response has no image data', function () {
    Storage::fake('cloudinary');

    OpenAI::fake([
        ImageCreateResponse::fake(['data' => [[]]]),
    ]);

    app(AiImageGenerator::class)->generate('A modern office', 'hero');
})->throws(AiGenerationException::class);
