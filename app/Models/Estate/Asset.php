<?php

declare(strict_types=1);

namespace App\Models\Estate;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset_type',
        'asset_name',
        'current_value',
        'ownership_type',
        'beneficiary_designation',
        'is_iht_exempt',
        'exemption_reason',
        'valuation_date',
    ];

    protected $casts = [
        'current_value' => 'float',
        'is_iht_exempt' => 'boolean',
        'valuation_date' => 'date',
    ];

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
