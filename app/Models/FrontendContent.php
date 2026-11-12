<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FrontendContent extends Model
{
    protected $fillable = [
        'key',
        'category',
        'default_value',
        'custom_value',
        'published_value',
        'company_id',
        'last_modified_by',
        'published_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function lastModifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(FrontendContentHistory::class, 'frontend_content_id')->latest();
    }

    public function isPendingPublication(): bool
    {
        return $this->custom_value !== null && $this->custom_value !== $this->published_value;
    }

    public function isCustomized(): bool
    {
        return ($this->published_value !== null && $this->published_value !== $this->default_value) ||
               ($this->custom_value !== null && $this->custom_value !== $this->default_value);
    }

    public function getEffectivePublishedValue(): string
    {
        return $this->published_value ?? $this->default_value;
    }

    public function getEffectiveDraftValue(): string
    {
        return $this->custom_value ?? $this->published_value ?? $this->default_value;
    }
}
