<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DC Pension Model
 *
 * Represents a Defined Contribution pension scheme (workplace, SIPP, or personal pension).
 */
class DCPension extends Model
{
    use HasFactory;

    protected $table = 'dc_pensions';

    protected $fillable = [
        'user_id',
        'scheme_name',
        'scheme_type',
        'provider',
        'member_number',
        'current_fund_value',
        'employee_contribution_percent',
        'employer_contribution_percent',
        'monthly_contribution_amount',
        'investment_strategy',
        'platform_fee_percent',
        'retirement_age',
        'projected_value_at_retirement',
    ];

    protected $casts = [
        'current_fund_value' => 'decimal:2',
        'employee_contribution_percent' => 'decimal:2',
        'employer_contribution_percent' => 'decimal:2',
        'monthly_contribution_amount' => 'decimal:2',
        'platform_fee_percent' => 'decimal:4',
        'retirement_age' => 'integer',
        'projected_value_at_retirement' => 'decimal:2',
    ];

    /**
     * Get the user that owns the DC pension.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
