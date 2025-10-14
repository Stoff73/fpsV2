<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class SavingsAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_type',
        'institution',
        'account_number',
        'current_balance',
        'interest_rate',
        'access_type',
        'notice_period_days',
        'maturity_date',
        'is_isa',
        'isa_type',
        'isa_subscription_year',
        'isa_subscription_amount',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'interest_rate' => 'decimal:4',
        'notice_period_days' => 'integer',
        'maturity_date' => 'date',
        'is_isa' => 'boolean',
        'isa_subscription_amount' => 'decimal:2',
    ];

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Encrypted account number accessor
     */
    protected function accountNumber(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Crypt::decryptString($value) : null,
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }
}
