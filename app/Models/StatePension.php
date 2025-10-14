<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * State Pension Model
 *
 * Represents UK State Pension information including NI contributions and forecast.
 */
class StatePension extends Model
{
    use HasFactory;

    protected $table = 'state_pensions';

    protected $fillable = [
        'user_id',
        'ni_years_completed',
        'ni_years_required',
        'state_pension_forecast_annual',
        'state_pension_age',
        'ni_gaps',
        'gap_fill_cost',
    ];

    protected $casts = [
        'ni_years_completed' => 'integer',
        'ni_years_required' => 'integer',
        'state_pension_forecast_annual' => 'decimal:2',
        'state_pension_age' => 'integer',
        'ni_gaps' => 'array',
        'gap_fill_cost' => 'decimal:2',
    ];

    /**
     * Get the user that owns the state pension record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
