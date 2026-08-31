<?php

namespace App\Actions\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class GenerateUniqueSlug
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function __invoke(string $modelClass, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 2;

        while (
            $modelClass::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
