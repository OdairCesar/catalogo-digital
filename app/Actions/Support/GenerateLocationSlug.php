<?php

namespace App\Actions\Support;

use App\Models\City;

final class GenerateLocationSlug
{
    public function __invoke(string $parentSlug, City $city): string
    {
        return "{$parentSlug}-em-{$city->slug}";
    }
}
