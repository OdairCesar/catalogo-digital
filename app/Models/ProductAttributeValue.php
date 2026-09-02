<?php

namespace App\Models;

use Database\Factories\ProductAttributeValueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read ProductAttribute $attribute
 */
class ProductAttributeValue extends Model
{
    /** @use HasFactory<ProductAttributeValueFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'product_attribute_id',
        'value',
        'hex',
        'image',
    ];

    /**
     * @return BelongsTo<ProductAttribute, $this>
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }

    public function label(): string
    {
        return "{$this->attribute->name}: {$this->value}";
    }

    public function imageUrl(): ?string
    {
        return $this->image !== null ? Storage::disk('cloudinary')->url($this->image) : null;
    }
}
