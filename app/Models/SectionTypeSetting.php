<?php

namespace App\Models;

use App\Enums\SectionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property SectionType $type
 * @property bool $enabled
 */
class SectionTypeSetting extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'type',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'type' => SectionType::class,
            'enabled' => 'boolean',
        ];
    }

    public const string CACHE_KEY = 'section-type.enabled-map';

    /**
     * Whether Cae has chosen to show this section type on the site. Defaults
     * to enabled when no row exists yet, so seeding order never hides
     * content unexpectedly.
     *
     * Every section type's flag is fetched together in one cached query
     * (`enabledMap()`) rather than one query per type, since a single page
     * render (e.g. the header/footer nav plus the home page blocks) can
     * check several types.
     */
    public static function isEnabled(SectionType $type): bool
    {
        return self::enabledMap()[$type->value] ?? true;
    }

    /**
     * @return array<string, bool>
     */
    public static function enabledMap(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn (): array => self::query()->pluck('enabled', 'type')->map(fn (mixed $enabled): bool => (bool) $enabled)->all(),
        );
    }
}
