<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'must_change_password',
        'date_of_birth',
        'gender',
        'marital_status',
        'spouse_id',
        'household_id',
        'is_primary_account',
        'role',
        'national_insurance_number',
        'address_line_1',
        'address_line_2',
        'city',
        'county',
        'postcode',
        'phone',
        'occupation',
        'employer',
        'industry',
        'employment_status',
        'annual_employment_income',
        'annual_self_employment_income',
        'annual_rental_income',
        'annual_dividend_income',
        'annual_other_income',
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
        'must_change_password' => 'boolean',
        'date_of_birth' => 'date',
        'is_primary_account' => 'boolean',
        'annual_employment_income' => 'decimal:2',
        'annual_self_employment_income' => 'decimal:2',
        'annual_rental_income' => 'decimal:2',
        'annual_dividend_income' => 'decimal:2',
        'annual_other_income' => 'decimal:2',
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

    /**
     * Get the household this user belongs to.
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /**
     * Get the user's spouse.
     */
    public function spouse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'spouse_id');
    }

    /**
     * Get the user's family members.
     */
    public function familyMembers(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    /**
     * Get the user's properties.
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Get the user's mortgages.
     */
    public function mortgages(): HasMany
    {
        return $this->hasMany(Mortgage::class);
    }

    /**
     * Get the user's liabilities.
     */
    public function liabilities(): HasMany
    {
        return $this->hasMany(\App\Models\Estate\Liability::class);
    }

    /**
     * Get the user's business interests.
     */
    public function businessInterests(): HasMany
    {
        return $this->hasMany(BusinessInterest::class);
    }

    /**
     * Get the user's chattels.
     */
    public function chattels(): HasMany
    {
        return $this->hasMany(Chattel::class);
    }

    /**
     * Get the user's cash accounts.
     */
    public function cashAccounts(): HasMany
    {
        return $this->hasMany(CashAccount::class);
    }

    /**
     * Get the user's personal account entries.
     */
    public function personalAccounts(): HasMany
    {
        return $this->hasMany(PersonalAccount::class);
    }

    /**
     * Get the user's investment accounts.
     */
    public function investmentAccounts(): HasMany
    {
        return $this->hasMany(\App\Models\Investment\InvestmentAccount::class);
    }

    /**
     * Get the user's DC (Defined Contribution) pensions.
     */
    public function dcPensions(): HasMany
    {
        return $this->hasMany(DCPension::class);
    }

    /**
     * Get the user's DB (Defined Benefit) pensions.
     */
    public function dbPensions(): HasMany
    {
        return $this->hasMany(DBPension::class);
    }

    /**
     * Get the user's state pension.
     */
    public function statePension(): HasOne
    {
        return $this->hasOne(StatePension::class);
    }

    /**
     * Get the spouse permission requests sent by this user
     */
    public function spousePermissionRequests(): HasMany
    {
        return $this->hasMany(SpousePermission::class, 'user_id');
    }

    /**
     * Get the spouse permission requests received by this user
     */
    public function receivedSpousePermissions(): HasMany
    {
        return $this->hasMany(SpousePermission::class, 'spouse_id');
    }

    /**
     * Check if user has accepted permission to share data with spouse
     */
    public function hasAcceptedSpousePermission(): bool
    {
        if (!$this->spouse_id) {
            return false;
        }

        $permission = SpousePermission::where(function ($query) {
            $query->where('user_id', $this->id)
                  ->where('spouse_id', $this->spouse_id);
        })->orWhere(function ($query) {
            $query->where('user_id', $this->spouse_id)
                  ->where('spouse_id', $this->id);
        })->where('status', 'accepted')->first();

        return $permission !== null;
    }
}
