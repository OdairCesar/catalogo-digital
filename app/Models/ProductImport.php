<?php

namespace App\Models;

use App\Enums\ProductImportStatus;
use Database\Factories\ProductImportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ProductImportStatus $status
 * @property array<string, mixed>|null $mapping
 * @property array<string, mixed>|null $result
 * @property-read Company $company
 */
class ProductImport extends Model
{
    /** @use HasFactory<ProductImportFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'company_id',
        'uploaded_by',
        'original_filename',
        'spreadsheet_path',
        'status',
        'mapping',
        'result',
        'ai_error',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductImportStatus::class,
            'mapping' => 'array',
            'result' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
