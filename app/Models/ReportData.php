<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportData extends Model
{
    use HasFactory;

    protected $table = 'report_data';

    protected $fillable = [
        'report_id',
        'section_key',
        'data_json',
    ];

    protected $casts = [
        'data_json' => 'array',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
