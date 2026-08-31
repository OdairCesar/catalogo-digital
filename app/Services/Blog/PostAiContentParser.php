<?php

namespace App\Services\Blog;

use App\Exceptions\AiGenerationException;
use App\Services\Ai\ValidatesJsonPayload;
use Illuminate\Support\Str;

final class PostAiContentParser
{
    use ValidatesJsonPayload;

    public function parse(string $jsonPayload): GeneratedPostContent
    {
        $data = json_decode($jsonPayload, associative: true);

        if (! is_array($data)) {
            throw AiGenerationException::invalidResponseShape();
        }

        return new GeneratedPostContent(
            title: $this->requireString($data, 'title'),
            excerpt: $this->requireString($data, 'excerpt'),
            contentHtml: Str::sanitizeHtml($this->requireString($data, 'content_html')),
            metaTitle: $this->requireString($data, 'meta_title'),
            metaDescription: $this->requireString($data, 'meta_description'),
            tags: $this->requireStringArray($data, 'tags'),
            imagePrompt: $this->requireString($data, 'image_prompt'),
        );
    }
}
