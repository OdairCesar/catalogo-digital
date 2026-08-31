<?php

use App\Enums\ServiceClusterStatus;
use App\Jobs\GenerateServiceClusterContent;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Services\ServiceCluster\ServiceClusterAiBrief;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Images\CreateResponse as ImageCreateResponse;

function fakeClusterBrief(int $serviceId): array
{
    return (new ServiceClusterAiBrief(serviceId: $serviceId, topic: 'Loja Virtual'))->toArray();
}

function fakeClusterChatResponse(): CreateResponse
{
    return CreateResponse::fake([
        'model' => 'gpt-4.1',
        'choices' => [[
            'message' => [
                'content' => json_encode([
                    'title' => 'Loja Virtual para Empresas de {cidade}',
                    'subtitle' => 'E-commerce rápido e seguro.',
                    'description' => 'Desenvolvemos lojas virtuais completas para empresas de {cidade}.',
                    'benefits' => ['Checkout otimizado', 'Integração com meios de pagamento'],
                    'faq' => [
                        ['question' => 'Quanto tempo leva?', 'answer' => 'Entre 4 e 8 semanas.'],
                    ],
                    'keywords' => ['loja virtual', 'e-commerce'],
                    'meta_title' => 'Loja Virtual | OD Tec',
                    'meta_description' => 'Crie sua loja virtual com a OD Tec.',
                    'image_prompt' => 'A modern e-commerce storefront on a laptop screen.',
                ]),
            ],
        ]],
    ]);
}

test('generating a cluster succeeds and stores the parsed ai response and hero image', function () {
    Storage::fake('cloudinary');

    OpenAI::fake([
        fakeClusterChatResponse(),
        ImageCreateResponse::fake([
            'data' => [['b64_json' => base64_encode('fake-image-bytes')]],
        ]),
    ]);

    $service = Service::factory()->create();
    $cluster = ServiceCluster::factory()->create([
        'service_id' => $service->id,
        'status' => ServiceClusterStatus::Generating,
        'ai_brief' => fakeClusterBrief($service->id),
    ]);

    Bus::dispatchSync(new GenerateServiceClusterContent($cluster));

    $cluster->refresh();

    expect($cluster->status)->toBe(ServiceClusterStatus::Draft)
        ->and($cluster->title)->toBe('Loja Virtual para Empresas de {cidade}')
        ->and($cluster->slug)->not->toBeNull()
        ->and($cluster->benefits)->toBe(['Checkout otimizado', 'Integração com meios de pagamento'])
        ->and($cluster->faq)->toBe([['question' => 'Quanto tempo leva?', 'answer' => 'Entre 4 e 8 semanas.']])
        ->and($cluster->keywords)->toBe(['loja virtual', 'e-commerce'])
        ->and($cluster->ai_model)->toBe('gpt-4.1')
        ->and($cluster->ai_error)->toBeNull()
        ->and($cluster->hero_image)->not->toBeNull();

    Storage::disk('cloudinary')->assertExists($cluster->hero_image);
});

test('a hero image generation failure still leaves the cluster as a draft without a hero image', function () {
    Storage::fake('cloudinary');

    OpenAI::fake([
        fakeClusterChatResponse(),
        new Exception('Unknown parameter: \'style\'.'),
    ]);

    $service = Service::factory()->create();
    $cluster = ServiceCluster::factory()->create([
        'service_id' => $service->id,
        'status' => ServiceClusterStatus::Generating,
        'ai_brief' => fakeClusterBrief($service->id),
    ]);

    Bus::dispatchSync(new GenerateServiceClusterContent($cluster));

    $cluster->refresh();

    expect($cluster->status)->toBe(ServiceClusterStatus::Draft)
        ->and($cluster->ai_error)->toBeNull()
        ->and($cluster->hero_image)->toBeNull();
});

test('an invalid ai response marks the cluster as failed with an error message', function () {
    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [[
                'message' => [
                    'content' => json_encode(['title' => 'Só o título, faltando os outros campos']),
                ],
            ]],
        ]),
    ]);

    $service = Service::factory()->create();
    $cluster = ServiceCluster::factory()->create([
        'service_id' => $service->id,
        'status' => ServiceClusterStatus::Generating,
        'ai_brief' => fakeClusterBrief($service->id),
    ]);

    Bus::dispatchSync(new GenerateServiceClusterContent($cluster));

    $cluster->refresh();

    expect($cluster->status)->toBe(ServiceClusterStatus::Failed)
        ->and($cluster->ai_error)->not->toBeNull();
});

test('the failed hook marks the cluster as failed with the exception message', function () {
    $service = Service::factory()->create();
    $cluster = ServiceCluster::factory()->create([
        'service_id' => $service->id,
        'status' => ServiceClusterStatus::Generating,
        'ai_brief' => fakeClusterBrief($service->id),
    ]);

    (new GenerateServiceClusterContent($cluster))->failed(new Exception('Falha de conexão com a OpenAI'));

    $cluster->refresh();

    expect($cluster->status)->toBe(ServiceClusterStatus::Failed)
        ->and($cluster->ai_error)->toBe('Falha de conexão com a OpenAI');
});
