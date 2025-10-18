<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessInterest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'household_id',
        'trust_id',
        'business_name',
        'company_number',
        'business_type',
        'ownership_type',
        'ownership_percentage',
        'current_valuation',
        'valuation_date',
        'valuation_method',
        'annual_revenue',
        'annual_profit',
        'annual_dividend_income',
        'description',
        'notes',
    ];

    protected $casts = [
        'valuation_date' => 'date',
        'current_valuation' => 'decimal:2',
        'ownership_percentage' => 'decimal:2',
        'annual_revenue' => 'decimal:2',
        'annual_profit' => 'decimal:2',
        'annual_dividend_income' => 'decimal:2',
    ];

    /**
     * Get the user that owns this business interest.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the household this business interest belongs to (for joint ownership).
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /**
     * Get the trust that holds this business interest (if applicable).
     */
    public function trust(): BelongsTo
    {
        return $this->belongsTo(Trust::class);
    }
}
