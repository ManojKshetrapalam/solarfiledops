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

    protected function dataJson(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                if (is_array($value)) {
                    return $value;
                }
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    // Handle possible double-encoding
                    if (is_string($decoded)) {
                        $second = json_decode($decoded, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($second)) {
                            return $second;
                        }
                    }
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return $decoded;
                    }
                }
                return [];
            },
            set: function ($value) {
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return json_encode($decoded);
                    }
                }
                return is_array($value) ? json_encode($value) : $value;
            }
        );
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
