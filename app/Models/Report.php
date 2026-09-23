<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_number',
        'service_id',
        'template_id',
        'company_id',
        'customer_id',
        'site_id',
        'engineer_id',
        'status',
        'current_step',
        'submitted_at',
        'reviewed_by_id',
        'reviewed_at',
        'approved_at',
        'correction_notes',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'current_step' => 'integer',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class, 'template_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'engineer_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ReportData::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ReportPhoto::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ReportDocument::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'resubmitted']);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isCorrectionRequired(): bool
    {
        return $this->status === 'correction_required';
    }

    public function isEditableByEngineer(): bool
    {
        return in_array($this->status, ['draft', 'correction_required']);
    }

    public function getSectionData(string $sectionKey, array $default = []): array
    {
        $section = $this->sections->firstWhere('section_key', $sectionKey);
        return $section ? ($section->data_json ?? $default) : $default;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'submitted', 'resubmitted' => 'bg-blue-100 text-blue-800 border-blue-200',
            'correction_required' => 'bg-amber-100 text-amber-800 border-amber-200',
            'rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
