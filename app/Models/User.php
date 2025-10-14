<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'date_of_birth',
        'gender',
        'marital_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
    ];

    /**
     * Get the user's protection profile.
     */
    public function protectionProfile(): HasOne
    {
        return $this->hasOne(ProtectionProfile::class);
    }

    /**
     * Get the user's life insurance policies.
     */
    public function lifeInsurancePolicies(): HasMany
    {
        return $this->hasMany(LifeInsurancePolicy::class);
    }

    /**
     * Get the user's critical illness policies.
     */
    public function criticalIllnessPolicies(): HasMany
    {
        return $this->hasMany(CriticalIllnessPolicy::class);
    }

    /**
     * Get the user's income protection policies.
     */
    public function incomeProtectionPolicies(): HasMany
    {
        return $this->hasMany(IncomeProtectionPolicy::class);
    }

    /**
     * Get the user's disability policies.
     */
    public function disabilityPolicies(): HasMany
    {
        return $this->hasMany(DisabilityPolicy::class);
    }

    /**
     * Get the user's sickness/illness policies.
     */
    public function sicknessIllnessPolicies(): HasMany
    {
        return $this->hasMany(SicknessIllnessPolicy::class);
    }
}
