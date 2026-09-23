<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_number',
        'company_id',
        'customer_id',
        'site_id',
        'service_type_id',
        'assigned_user_id',
        'priority',
        'scheduled_date',
        'description',
        'status',
        'created_by_id',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

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

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function assignedEngineer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ServiceAssignment::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(Report::class);
    }

    public function isAssignedTo(int $userId): bool
    {
        return $this->assigned_user_id === $userId;
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'bg-red-100 text-red-800 border-red-200',
            'high' => 'bg-amber-100 text-amber-800 border-amber-200',
            'normal' => 'bg-blue-100 text-blue-800 border-blue-200',
            default => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'in_progress' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'assigned' => 'bg-blue-100 text-blue-800 border-blue-200',
            'report_submitted' => 'bg-purple-100 text-purple-800 border-purple-200',
            'correction_required' => 'bg-amber-100 text-amber-800 border-amber-200',
            'cancelled' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
