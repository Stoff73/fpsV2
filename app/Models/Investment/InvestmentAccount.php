<?php

declare(strict_types=1);

namespace App\Models\Investment;

use App\Models\Household;
use App\Models\Trust;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'household_id',
        'trust_id',
        'ownership_type',
        'ownership_percentage',
        'account_type',
        'provider',
        'account_number',
        'platform',
        'current_value',
        'contributions_ytd',
        'tax_year',
        'platform_fee_percent',
        'isa_type',
        'isa_subscription_current_year',
    ];

    protected $casts = [
        'current_value' => 'float',
        'contributions_ytd' => 'float',
        'platform_fee_percent' => 'float',
        'isa_subscription_current_year' => 'float',
        'ownership_percentage' => 'decimal:2',
    ];

    protected $attributes = [
        'contributions_ytd' => 0,
        'platform_fee_percent' => 0,
        'isa_subscription_current_year' => 0,
    ];

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Holdings relationship
     */
    public function holdings(): HasMany
    {
        return $this->hasMany(Holding::class);
    }

    /**
     * Get the household this investment account belongs to (for joint ownership).
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /**
     * Get the trust that holds this investment account (if applicable).
     */
    public function trust(): BelongsTo
    {
        return $this->belongsTo(Trust::class);
    }
}
