<?php

namespace App\Models;

use App\Enums\PageStatus;
use App\Enums\Weekday;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * @property PageStatus $status
 */
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'site_name',
        'slug',
        'cnpj',
        'whatsapp',
        'email',
        'address_zip_code',
        'address_street',
        'address_number',
        'address_complement',
        'address_neighborhood',
        'address_city',
        'address_state',
        'instagram_url',
        'facebook_url',
        'opening_hours',
        'short_description',
        'logo',
        'favicon',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'opening_hours' => 'array',
        ];
    }

    /**
     * The single company record used to power the site's own institutional
     * data (footer, header, SEO) — distinct from `status`, which governs
     * whether this company's product catalog is publicly visible.
     *
     * Caches the raw attributes rather than the hydrated model: caching an
     * Eloquent instance directly risks `__PHP_Incomplete_Class` on unserialize
     * when the cache is written and read from different processes (e.g. a
     * `tinker` run vs the web server) on non-array cache stores.
     */
    public static function current(): ?self
    {
        $attributes = Cache::rememberForever(
            self::cacheKey(),
            fn (): ?array => self::query()->oldest('id')->first()?->getAttributes(),
        );

        return $attributes !== null ? (new self)->newFromBuilder($attributes) : null;
    }

    public static function cacheKey(): string
    {
        return 'company.current';
    }

    public function displayName(): string
    {
        return $this->site_name ?? $this->name;
    }

    public function logoUrl(): ?string
    {
        return $this->logo !== null ? Storage::disk('cloudinary')->url($this->logo) : null;
    }

    public function faviconUrl(): ?string
    {
        return $this->favicon !== null ? Storage::disk('cloudinary')->url($this->favicon) : null;
    }

    /**
     * The site's display name, falling back to a default when there is no
     * company record yet. Used for SEO titles, JSON-LD, and the admin brand.
     */
    public static function siteName(string $default = 'Fit By Cae'): string
    {
        return self::current()?->displayName() ?? $default;
    }

    /**
     * Digits-only WhatsApp number (with the country code), ready for a
     * `wa.me` link.
     */
    public function whatsappDigits(): ?string
    {
        if ($this->whatsapp === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->whatsapp);

        // A Brazilian number with its DDD is 10-11 digits; 12-13 means the
        // country code is already present. Checking length (not a '55'
        // prefix) avoids misreading a DDD 55 area code (e.g. Porto Alegre)
        // as the country code already being there.
        return in_array(strlen($digits), [12, 13], true) ? $digits : '55'.$digits;
    }

    /**
     * A `wa.me` link to start a WhatsApp conversation, optionally with a
     * pre-filled message. Returns null when there's no WhatsApp number set.
     */
    public function whatsappLink(?string $message = null): ?string
    {
        $digits = $this->whatsappDigits();

        if ($digits === null) {
            return null;
        }

        return $message === null
            ? "https://wa.me/{$digits}"
            : "https://wa.me/{$digits}?text=".rawurlencode($message);
    }

    /**
     * Single-line address for display, omitting parts that aren't filled in.
     */
    public function formattedAddress(): ?string
    {
        $parts = array_filter([
            $this->streetLine(),
            $this->address_complement,
            $this->address_neighborhood,
            collect([$this->address_city, $this->address_state])->filter()->implode('/'),
            $this->address_zip_code,
        ], fn (?string $part): bool => filled($part));

        return $parts === [] ? null : implode(' - ', $parts);
    }

    /**
     * "Street, number" combined, omitting either part that isn't filled in.
     */
    public function streetLine(): ?string
    {
        $line = collect([$this->address_street, $this->address_number])->filter()->implode(', ');

        return $line !== '' ? $line : null;
    }

    /**
     * Human-readable opening hours, grouping consecutive days that share the
     * same schedule (e.g. "Seg a Sex: 08:00 às 18:00, Sáb: 08:00 às 12:00").
     * Days without hours set, or marked closed, are omitted.
     */
    public function openingHoursDisplay(): ?string
    {
        if ($this->opening_hours === null) {
            return null;
        }

        $days = Weekday::cases();
        $lines = [];
        $groupStart = 0;

        for ($i = 0; $i <= count($days); $i++) {
            $current = $i < count($days) ? ($this->opening_hours[$days[$i]->value] ?? null) : null;
            $anchor = $this->opening_hours[$days[$groupStart]->value] ?? null;

            $sameAsAnchor = $current !== null && $anchor !== null
                && ($current['closed'] ?? false) === ($anchor['closed'] ?? false)
                && ($current['open'] ?? null) === ($anchor['open'] ?? null)
                && ($current['close'] ?? null) === ($anchor['close'] ?? null);

            if ($sameAsAnchor) {
                continue;
            }

            $line = $this->openingHoursLine($days[$groupStart], $days[$i - 1], $anchor);

            if ($line !== null) {
                $lines[] = $line;
            }

            $groupStart = $i;
        }

        return $lines === [] ? null : implode(', ', $lines);
    }

    /**
     * @param  array{closed: bool, open: ?string, close: ?string}|null  $hours
     */
    private function openingHoursLine(Weekday $from, Weekday $to, ?array $hours): ?string
    {
        if ($hours === null || ($hours['closed'] ?? false)) {
            return null;
        }

        if (! filled($hours['open'] ?? null) || ! filled($hours['close'] ?? null)) {
            return null;
        }

        $range = $from === $to ? $from->shortLabel() : "{$from->shortLabel()} a {$to->shortLabel()}";

        return "{$range}: {$hours['open']} às {$hours['close']}";
    }

    /**
     * @return HasMany<Store, $this>
     */
    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @param  Builder<Company>  $query
     * @return Builder<Company>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PageStatus::Published);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
