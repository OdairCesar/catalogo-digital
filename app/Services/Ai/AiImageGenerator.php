<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class AiImageGenerator
{
    /**
     * Always applied so every generated image matches Fit By Cae's brand
     * identity, regardless of what the caller's prompt describes. See
     * desing-and-context/fit_by_cae_contexto_marca.md for the full brief.
     */
    private const BRAND_DIRECTION = 'The image must align with the visual identity of Fit By Cae, a Brazilian '
        .'fitness-apparel brand for women that celebrates real, diverse bodies. Show models with a real, diverse '
        .'body type (not just thin/toned as the default), diverse skin tones, natural skin texture (visible pores, '
        .'no plastic photoshopped look), confident and natural expressions, warm natural lighting (golden hour or '
        .'soft studio light, never cold/clinical). Favor authentic, lifestyle-style photography over generic stock-'
        .'photo or corporate-studio compositions. Color palette accents: purple (#9647B2), dark blue-gray '
        .'(#1C2839), soft lilac (#E9D6F0). Avoid a single idealized thin body type, visible photoshop, sexualized '
        .'poses, or watermarks/logos from other brands.';

    public function __construct(private readonly ImageProvider $provider) {}

    /**
     * Generates an image via the configured AI provider and stores it on the
     * cloudinary disk, returning the stored path.
     */
    public function generate(string $prompt, string $filenameSuffix): string
    {
        $contents = $this->provider->generateImageBytes($prompt.' '.self::BRAND_DIRECTION);

        $path = Str::ulid().'-'.$filenameSuffix;

        Storage::disk('cloudinary')->put($path, $contents);

        return $path;
    }
}
