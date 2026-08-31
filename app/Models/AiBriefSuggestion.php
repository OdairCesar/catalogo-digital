<?php

namespace App\Models;

use Database\Factories\AiBriefSuggestionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $field
 * @property string $value
 */
class AiBriefSuggestion extends Model
{
    /** @use HasFactory<AiBriefSuggestionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'field',
        'value',
    ];

    /**
     * @param  Builder<AiBriefSuggestion>  $query
     * @return Builder<AiBriefSuggestion>
     */
    public function scopeForField(Builder $query, string $field): Builder
    {
        return $query->where('field', $field);
    }

    public static function remember(string $field, ?string $value): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            return;
        }

        static::query()->firstOrCreate(['field' => $field, 'value' => $value]);
    }
}
