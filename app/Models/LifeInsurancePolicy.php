<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifeInsurancePolicy extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'policy_type',
        'provider',
        'policy_number',
        'sum_assured',
        'premium_amount',
        'premium_frequency',
        'policy_start_date',
        'policy_term_years',
        'indexation_rate',
        'in_trust',
        'beneficiaries',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sum_assured' => 'decimal:2',
        'premium_amount' => 'decimal:2',
        'indexation_rate' => 'decimal:4',
        'policy_start_date' => 'date',
        'policy_term_years' => 'integer',
        'in_trust' => 'boolean',
    ];

    /**
     * Get the user that owns the policy.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
