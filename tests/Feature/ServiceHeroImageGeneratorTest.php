<?php

use App\Services\Landing\ServiceHeroImageGenerator;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Resources\Images;
use OpenAI\Responses\Images\CreateResponse as ImageCreateResponse;

test('generate builds a prompt from the service fields and stores the image on the cloudinary disk', function () {
    Storage::fake('cloudinary');

    OpenAI::fake([
        ImageCreateResponse::fake([
            'data' => [['b64_json' => base64_encode('fake-image-bytes')]],
        ]),
    ]);

    $path = app(ServiceHeroImageGenerator::class)->generate(
        title: 'Criação de Sites',
        subtitle: 'Sites rápidos e profissionais',
        description: 'Criamos sites modernos e otimizados.',
        benefits: ['Rápido', 'Responsivo'],
    );

    expect($path)->toEndWith('-hero');

    Storage::disk('cloudinary')->assertExists($path);

    OpenAI::assertSent(Images::class, function (string $method, array $parameters): bool {
        return $method === 'create'
            && str_contains($parameters['prompt'], 'Criação de Sites')
            && str_contains($parameters['prompt'], 'Rápido, Responsivo');
    });
});
