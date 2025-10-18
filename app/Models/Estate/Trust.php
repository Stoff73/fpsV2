<?php

namespace App\Models\Estate;

use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trust extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'household_id',
        'trust_name',
        'trust_type',
        'trust_creation_date',
        'initial_value',
        'current_value',
        'discount_amount',
        'retained_income_annual',
        'loan_amount',
        'loan_interest_bearing',
        'loan_interest_rate',
        'sum_assured',
        'annual_premium',
        'is_relevant_property_trust',
        'last_periodic_charge_date',
        'last_periodic_charge_amount',
        'last_valuation_date',
        'next_tax_return_due',
        'total_asset_value',
        'beneficiaries',
        'trustees',
        'purpose',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'trust_creation_date' => 'date',
        'last_periodic_charge_date' => 'date',
        'last_valuation_date' => 'date',
        'next_tax_return_due' => 'date',
        'initial_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'retained_income_annual' => 'decimal:2',
        'loan_amount' => 'decimal:2',
        'loan_interest_rate' => 'decimal:4',
        'sum_assured' => 'decimal:2',
        'annual_premium' => 'decimal:2',
        'last_periodic_charge_amount' => 'decimal:2',
        'total_asset_value' => 'decimal:2',
        'loan_interest_bearing' => 'boolean',
        'is_relevant_property_trust' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the household this trust belongs to.
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /**
     * Get the IHT value of this trust (value that counts toward estate)
     */
    public function getIHTValue(): float
    {
        switch ($this->trust_type) {
            case 'bare':
                // Bare trusts - beneficiary absolutely entitled, counts in beneficiary's estate
                return 0;

            case 'discounted_gift':
                // Discounted gift trust - only retained income stream counts
                return $this->discount_amount ?? 0;

            case 'loan':
                // Loan trust - outstanding loan counts in estate, growth doesn't
                return $this->loan_amount ?? 0;

            case 'life_insurance':
                // Life insurance in trust - outside estate
                return 0;

            case 'interest_in_possession':
                // Depends if qualifying or non-qualifying - simplified: assume qualifying
                // Qualifying IIP - counts in life tenant's estate
                return $this->current_value;

            case 'discretionary':
            case 'accumulation_maintenance':
                // Relevant property trusts - outside settlor's estate but subject to periodic charges
                return 0;

            default:
                return 0;
        }
    }

    /**
     * Check if trust is a relevant property trust (subject to 10-year charges)
     */
    public function isRelevantPropertyTrust(): bool
    {
        return in_array($this->trust_type, [
            'discretionary',
            'accumulation_maintenance',
        ]) || $this->is_relevant_property_trust;
    }
}
