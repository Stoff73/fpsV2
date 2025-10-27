<?php

declare(strict_types=1);

namespace App\Models\Estate;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Liability extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'liability_type',
        'country',
        'liability_name',
        'current_balance',
        'monthly_payment',
        'interest_rate',
        'maturity_date',
        'secured_against',
        'is_priority_debt',
        'mortgage_type',
        'fixed_until',
        'notes',
    ];

    protected $casts = [
        'current_balance' => 'float',
        'monthly_payment' => 'float',
        'interest_rate' => 'float',
        'maturity_date' => 'date',
        'is_priority_debt' => 'boolean',
        'fixed_until' => 'date',
    ];

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
