<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'status',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
