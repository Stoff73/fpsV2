<?php

declare(strict_types=1);

namespace App\Models\Investment;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_version',
        'plan_data',
        'portfolio_health_score',
        'is_complete',
        'completeness_score',
        'generated_at',
    ];

    protected $casts = [
        'plan_data' => 'array',
        'is_complete' => 'boolean',
        'portfolio_health_score' => 'integer',
        'completeness_score' => 'integer',
        'generated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the plan
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recommendations for this plan
     */
    public function recommendations(): HasMany
    {
        return $this->hasMany(InvestmentRecommendation::class);
    }
}
