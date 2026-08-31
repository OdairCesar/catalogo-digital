<?php

namespace App\Models;

trait HasStoreInventoryOverride
{
    /**
     * Resolves a value through the store-override → own-value → fallback
     * cascade shared by every effective*() getter: the first non-null value
     * among the store override and the given fallbacks wins.
     */
    protected function effectiveValue(string $storeColumn, ?Store $store, mixed ...$fallbacks): mixed
    {
        $value = $this->storeOverride($storeColumn, $store);

        foreach ($fallbacks as $fallback) {
            if ($value !== null) {
                break;
            }

            $value = $fallback;
        }

        return $value;
    }

    protected function storeOverride(string $column, ?Store $store): mixed
    {
        if (! $store instanceof Store) {
            return null;
        }

        return $this->inventories()->where('store_id', $store->id)->value($column);
    }
}
