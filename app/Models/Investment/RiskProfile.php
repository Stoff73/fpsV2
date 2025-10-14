<?php

declare(strict_types=1);

namespace App\Models\Investment;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'risk_tolerance',
        'capacity_for_loss_percent',
        'time_horizon_years',
        'knowledge_level',
        'attitude_to_volatility',
        'esg_preference',
    ];

    protected $casts = [
        'capacity_for_loss_percent' => 'float',
        'time_horizon_years' => 'integer',
        'esg_preference' => 'boolean',
    ];

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
