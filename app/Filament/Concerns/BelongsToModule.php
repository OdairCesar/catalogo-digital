<?php

namespace App\Filament\Concerns;

use App\Enums\SiteModule;

trait BelongsToModule
{
    abstract protected static function module(): SiteModule;

    public static function shouldRegisterNavigation(): bool
    {
        return static::module()->isEnabled() && static::$shouldRegisterNavigation;
    }

    public static function canAccess(): bool
    {
        return static::module()->isEnabled() && static::canViewAny();
    }
}
