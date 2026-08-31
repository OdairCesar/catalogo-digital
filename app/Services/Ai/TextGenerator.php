<?php

namespace App\Services\Ai;

interface TextGenerator
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function generate(array $messages, JsonSchema $schema, float $temperature): TextGenerationResult;
}
