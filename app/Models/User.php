<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'employee_code',
        'phone',
        'designation',
        'company_id',
        'status',
        'profile_photo_path',
        'joining_date',
        'password_change_required',
        'first_login_completed',
        'password_changed_at',
        'last_login_at',
        'temporary_password_encrypted',
        'temporary_password_created_at',
        'temporary_password_expires_at',
        'temporary_password_consumed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'temporary_password_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'joining_date' => 'date',
            'password_change_required' => 'boolean',
            'first_login_completed' => 'boolean',
            'password_changed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'temporary_password_created_at' => 'datetime',
            'temporary_password_expires_at' => 'datetime',
            'temporary_password_consumed_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEngineer(): bool
    {
        return $this->role === 'engineer';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedServices(): HasMany
    {
        return $this->hasMany(Service::class, 'assigned_user_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'engineer_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function dataImports(): HasMany
    {
        return $this->hasMany(DataImport::class);
    }

    public function unreadNotificationsCount(): int
    {
        return $this->hasMany(Notification::class)->whereNull('read_at')->count();
    }

    public function hasExpiredTemporaryPassword(): bool
    {
        if (!$this->password_change_required) {
            return false;
        }

        return $this->temporary_password_expires_at && now()->gt($this->temporary_password_expires_at);
    }

    public function getDecryptedTemporaryPassword(): ?string
    {
        if (!$this->temporary_password_encrypted) {
            return null;
        }

        try {
            return Crypt::decryptString($this->temporary_password_encrypted);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function setTemporaryPassword(string $plainText, int $validDays = 7): void
    {
        $this->password = Hash::make($plainText);
        $this->temporary_password_encrypted = Crypt::encryptString($plainText);
        $this->temporary_password_created_at = now();
        $this->temporary_password_expires_at = now()->addDays($validDays);
        $this->password_change_required = true;
    }

    public function consumeTemporaryPassword(): void
    {
        $this->password_change_required = false;
        $this->first_login_completed = true;
        $this->password_changed_at = now();
        $this->temporary_password_consumed_at = now();
        $this->temporary_password_encrypted = null;
    }

    public function getLoginStatusAttribute(): string
    {
        if ($this->status !== 'active') {
            return 'Deactivated';
        }

        if (!$this->first_login_completed && $this->password_change_required) {
            return 'Pending First Login';
        }

        if ($this->password_change_required) {
            return 'Password Change Required';
        }

        return 'Active — Password Changed';
    }

    public static function generateUniqueUsername(string $firstName, ?string $lastName = null, ?int $ignoreId = null): string
    {
        $first = Str::slug(strtolower(trim($firstName)), '');
        if (empty($first)) {
            $first = 'user';
        }

        $candidate = $first;
        if (!self::where('username', $candidate)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            return $candidate;
        }

        if (!empty($lastName)) {
            $lastInitial = strtolower(substr(trim($lastName), 0, 1));
            $candidateWithInitial = $first . '.' . $lastInitial;
            if (!self::where('username', $candidateWithInitial)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
                return $candidateWithInitial;
            }
        }

        $counter = 2;
        while (self::where('username', $first . $counter)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $counter++;
        }

        return $first . $counter;
    }
}
