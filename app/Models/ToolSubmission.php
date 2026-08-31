<?php

namespace App\Models;

use App\Enums\ToolSubmissionStatus;
use Database\Factories\ToolSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $tool_slug
 * @property array<int, array{role: string, content: string}> $messages
 * @property ToolSubmissionStatus $status
 * @property array<string, mixed>|null $result
 */
class ToolSubmission extends Model
{
    /** @use HasFactory<ToolSubmissionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'tool_slug',
        'name',
        'email',
        'phone',
        'messages',
        'status',
        'questions_asked',
        'result',
        'ai_model',
        'ai_error',
        'source_url',
        'read_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'messages' => 'array',
            'status' => ToolSubmissionStatus::class,
            'result' => 'array',
            'read_at' => 'datetime',
        ];
    }
}
