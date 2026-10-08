<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_type',
        'file_name',
        'file_path',
        'user_id',
        'total_rows',
        'imported_count',
        'skipped_count',
        'failed_count',
        'status',
        'summary_data',
        'error_log',
    ];

    protected $casts = [
        'summary_data' => 'array',
        'error_log' => 'array',
        'total_rows' => 'integer',
        'imported_count' => 'integer',
        'skipped_count' => 'integer',
        'failed_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
