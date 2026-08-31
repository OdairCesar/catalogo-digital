<?php

use App\Exceptions\AiGenerationException;
use App\Services\Portfolio\PortfolioCopyGenerator;
use Illuminate\Support\Facades\Http;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;

test('generate fetches the project page, prompts the ai and returns the parsed title and excerpt', function () {
    Http::fake([
        'https://cliente-exemplo.com/*' => Http::response('<html><body><h1>Projeto incrível</h1></body></html>', 200),
    ]);

    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'title' => 'Plataforma de agendamento para clínicas',
                        'excerpt' => 'Sistema que reduziu o tempo de agendamento em 40%.',
                    ]),
                ],
            ]],
        ]),
    ]);

    $copy = app(PortfolioCopyGenerator::class)->generate('https://cliente-exemplo.com/projeto', 'Desenvolvimento de sistemas');

    expect($copy)->toBe([
        'title' => 'Plataforma de agendamento para clínicas',
        'excerpt' => 'Sistema que reduziu o tempo de agendamento em 40%.',
    ]);
});

test('generate rejects internal urls without making an http request', function (string $url) {
    Http::fake();

    try {
        app(PortfolioCopyGenerator::class)->generate($url, null);
        $this->fail('Expected AiGenerationException was not thrown.');
    } catch (AiGenerationException) {
        Http::assertNothingSent();
    }
})->with([
    'http://169.254.169.254/latest/meta-data/',
    'http://localhost/admin',
    'ftp://example.com/file',
]);

test('generate throws when the project url cannot be reached', function () {
    Http::fake([
        'https://cliente-exemplo.com/*' => Http::response('', 500),
    ]);

    app(PortfolioCopyGenerator::class)->generate('https://cliente-exemplo.com/projeto', null);
})->throws(AiGenerationException::class);

test('generate throws when the ai response is missing the expected fields', function () {
    Http::fake([
        'https://cliente-exemplo.com/*' => Http::response('<html><body>Conteúdo</body></html>', 200),
    ]);

    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [[
                'message' => ['content' => json_encode(['title' => 'Só o título'])],
            ]],
        ]),
    ]);

    app(PortfolioCopyGenerator::class)->generate('https://cliente-exemplo.com/projeto', null);
})->throws(AiGenerationException::class);
