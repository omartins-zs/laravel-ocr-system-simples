<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OcrDocument extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'original_name',
        'stored_path',
        'mime_type',
        'extension',
        'file_size',
        'extracted_text',
        'status',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function excerpt(int $limit = 120): string
    {
        if (blank($this->extracted_text)) {
            return '-';
        }

        return Str::limit(preg_replace('/\s+/u', ' ', trim((string) $this->extracted_text)), $limit);
    }
}
