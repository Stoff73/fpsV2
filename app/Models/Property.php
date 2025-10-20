<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Estate\Trust;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'household_id',
        'trust_id',
        'property_type',
        'ownership_type',
        'ownership_percentage',
        'address_line_1',
        'address_line_2',
        'city',
        'county',
        'postcode',
        'purchase_date',
        'purchase_price',
        'current_value',
        'valuation_date',
        'sdlt_paid',
        'monthly_rental_income',
        'annual_rental_income',
        'outstanding_mortgage',
        'occupancy_rate_percent',
        'tenant_name',
        'lease_start_date',
        'lease_end_date',
        'annual_service_charge',
        'annual_ground_rent',
        'annual_insurance',
        'annual_maintenance_reserve',
        'other_annual_costs',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'valuation_date' => 'date',
        'lease_start_date' => 'date',
        'lease_end_date' => 'date',
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
        'sdlt_paid' => 'decimal:2',
        'monthly_rental_income' => 'decimal:2',
        'annual_rental_income' => 'decimal:2',
        'outstanding_mortgage' => 'decimal:2',
        'annual_service_charge' => 'decimal:2',
        'annual_ground_rent' => 'decimal:2',
        'annual_insurance' => 'decimal:2',
        'annual_maintenance_reserve' => 'decimal:2',
        'other_annual_costs' => 'decimal:2',
        'ownership_percentage' => 'decimal:2',
        'occupancy_rate_percent' => 'integer',
    ];

    /**
     * Get the user that owns this property.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the household this property belongs to (for joint ownership).
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /**
     * Get the trust that holds this property (if applicable).
     */
    public function trust(): BelongsTo
    {
        return $this->belongsTo(Trust::class);
    }

    /**
     * Get the mortgages associated with this property.
     */
    public function mortgages(): HasMany
    {
        return $this->hasMany(Mortgage::class);
    }
}
