<?php

namespace App\Services\Testimonials;

use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final readonly class TestimonialPresenter
{
    /**
     * @param  Collection<int, Section>  $testimonials
     * @return list<array{text: ?string, initial: string, name: ?string, detail: ?string, extra_fields: array<int, array{label: string, value: string}>}>
     */
    public static function present(Collection $testimonials): array
    {
        return $testimonials
            ->map(fn (Section $testimonial): array => [
                'text' => $testimonial->content,
                'name' => $testimonial->data['author_name'] ?? null,
                'detail' => $testimonial->data['author_detail'] ?? null,
                'initial' => Str::upper(Str::substr($testimonial->data['author_name'] ?? '', 0, 1)),
                'extra_fields' => $testimonial->extra_fields ?? [],
            ])
            ->all();
    }
}
