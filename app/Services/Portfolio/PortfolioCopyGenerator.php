<?php

namespace App\Services\Portfolio;

use App\Exceptions\AiGenerationException;
use App\Services\Ai\JsonSchema;
use App\Services\Ai\TextGenerator;
use App\Services\Ai\ValidatesJsonPayload;
use App\Support\OutboundUrlGuard;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Throwable;

final class PortfolioCopyGenerator
{
    use ValidatesJsonPayload;

    public function __construct(private readonly TextGenerator $textGenerator) {}

    /**
     * @return array{title: string, excerpt: string}
     */
    public function generate(string $url, ?string $serviceTitle): array
    {
        $pageText = $this->fetchPageText($url);

        $result = $this->textGenerator->generate(
            [
                ['role' => 'system', 'content' => $this->systemPrompt()],
                ['role' => 'user', 'content' => $this->userPrompt($pageText, $serviceTitle)],
            ],
            $this->responseFormat(),
            0.7,
        );

        return $this->parse($result->content);
    }

    private function fetchPageText(string $url): string
    {
        try {
            OutboundUrlGuard::assertSafe($url);
        } catch (InvalidArgumentException $exception) {
            throw AiGenerationException::blockedUrl($exception);
        }

        try {
            $response = Http::withOptions([
                // Redirects are followed but each hop is re-validated, so a public
                // URL cannot 30x its way into an internal host.
                'allow_redirects' => [
                    'max' => 3,
                    'strict' => true,
                    'protocols' => ['http', 'https'],
                    'on_redirect' => static function (mixed $request, mixed $response, UriInterface $uri): void {
                        OutboundUrlGuard::assertSafe((string) $uri);
                    },
                ],
            ])->timeout(15)->get($url);
        } catch (Throwable $exception) {
            throw AiGenerationException::unreachableUrl($exception);
        }

        if (! $response->successful()) {
            throw AiGenerationException::unreachableUrl();
        }

        return Str::of(strip_tags($response->body()))
            ->squish()
            ->limit(4000)
            ->toString();
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
            Você é um redator de portfólio para a OD Tec, uma empresa de tecnologia. A partir do texto extraído
            da página de um projeto entregue, escreva um título curto e um resumo (excerpt) convincente em
            português do Brasil, destacando o valor entregue ao cliente. Responda estritamente no formato
            JSON solicitado.
            PROMPT;
    }

    private function userPrompt(string $pageText, ?string $serviceTitle): string
    {
        $lines = [];

        if ($serviceTitle) {
            $lines[] = "Serviço prestado: {$serviceTitle}";
        }

        $lines[] = "Conteúdo extraído da página do projeto:\n{$pageText}";

        return implode("\n", $lines);
    }

    private function responseFormat(): JsonSchema
    {
        return new JsonSchema(
            name: 'portfolio_copy',
            schema: [
                'type' => 'object',
                'required' => ['title', 'excerpt'],
                'properties' => [
                    'title' => ['type' => 'string'],
                    'excerpt' => ['type' => 'string'],
                ],
            ],
        );
    }

    /**
     * @return array{title: string, excerpt: string}
     */
    private function parse(string $jsonPayload): array
    {
        $data = json_decode($jsonPayload, associative: true);

        if (! is_array($data)) {
            throw AiGenerationException::invalidResponseShape();
        }

        return [
            'title' => $this->requireString($data, 'title'),
            'excerpt' => $this->requireString($data, 'excerpt'),
        ];
    }
}
