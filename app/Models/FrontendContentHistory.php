<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrontendContentHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'frontend_content_id',
        'action',
        'old_value',
        'new_value',
        'user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(FrontendContent::class, 'frontend_content_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
