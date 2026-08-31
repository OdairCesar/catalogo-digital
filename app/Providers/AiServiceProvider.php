<?php

namespace App\Providers;

use App\Services\Ai\Embedder;
use App\Services\Ai\Gemini\GeminiEmbedder;
use App\Services\Ai\Gemini\GeminiImageProvider;
use App\Services\Ai\Gemini\GeminiTextGenerator;
use App\Services\Ai\ImageProvider;
use App\Services\Ai\OpenAi\OpenAiEmbedder;
use App\Services\Ai\OpenAi\OpenAiImageProvider;
use App\Services\Ai\OpenAi\OpenAiTextGenerator;
use App\Services\Ai\TextGenerator;
use Closure;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

final class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            TextGenerator::class,
            $this->providerResolver('services.ai.text_provider', GeminiTextGenerator::class, OpenAiTextGenerator::class),
        );

        $this->app->singleton(
            ImageProvider::class,
            $this->providerResolver('services.ai.image_provider', GeminiImageProvider::class, OpenAiImageProvider::class),
        );

        $this->app->singleton(
            Embedder::class,
            $this->providerResolver('services.ai.text_provider', GeminiEmbedder::class, OpenAiEmbedder::class),
        );
    }

    /**
     * @param  class-string  $geminiClass
     * @param  class-string  $openAiClass
     */
    private function providerResolver(string $configKey, string $geminiClass, string $openAiClass): Closure
    {
        return function () use ($configKey, $geminiClass, $openAiClass): object {
            $provider = config()->string($configKey);

            return match ($provider) {
                'gemini' => $this->app->make($geminiClass),
                'openai' => $this->app->make($openAiClass),
                default => throw new InvalidArgumentException("Unsupported AI provider [{$provider}] for config [{$configKey}]."),
            };
        };
    }
}
