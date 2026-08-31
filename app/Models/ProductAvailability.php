<?php

namespace App\Models;

use Database\Factories\ProductAvailabilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $weekday 0 (domingo) a 6 (sábado), igual ao Carbon::dayOfWeek.
 */
class ProductAvailability extends Model
{
    /** @use HasFactory<ProductAvailabilityFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'weekday',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
