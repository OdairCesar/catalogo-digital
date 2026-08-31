<?php

use App\Services\Portfolio\PortfolioCoverImageGenerator;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Resources\Images;
use OpenAI\Responses\Images\CreateResponse as ImageCreateResponse;

test('generate builds a prompt from the portfolio item fields and stores the image on the cloudinary disk', function () {
    Storage::fake('cloudinary');

    OpenAI::fake([
        ImageCreateResponse::fake([
            'data' => [['b64_json' => base64_encode('fake-image-bytes')]],
        ]),
    ]);

    $path = app(PortfolioCoverImageGenerator::class)->generate(
        title: 'Plataforma de agendamento para clínicas',
        excerpt: 'Sistema que reduziu o tempo de agendamento em 40%.',
        serviceTitle: 'Desenvolvimento de sistemas',
    );

    expect($path)->toEndWith('-portfolio');

    Storage::disk('cloudinary')->assertExists($path);

    OpenAI::assertSent(Images::class, function (string $method, array $parameters): bool {
        return $method === 'create'
            && str_contains($parameters['prompt'], 'Plataforma de agendamento para clínicas')
            && str_contains($parameters['prompt'], 'Desenvolvimento de sistemas');
    });
});
