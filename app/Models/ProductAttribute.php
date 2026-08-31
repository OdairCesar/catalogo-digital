<?php

namespace App\Models;

use Database\Factories\ProductAttributeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttribute extends Model
{
    /** @use HasFactory<ProductAttributeFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<ProductAttributeValue, $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
}
