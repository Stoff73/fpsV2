<?php

declare(strict_types=1);

namespace App\Models\Investment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holding extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_account_id',
        'asset_type',
        'allocation_percent',
        'security_name',
        'ticker',
        'isin',
        'quantity',
        'purchase_price',
        'purchase_date',
        'current_price',
        'current_value',
        'cost_basis',
        'dividend_yield',
        'ocf_percent',
    ];

    protected $casts = [
        'allocation_percent' => 'float',
        'quantity' => 'float',
        'purchase_price' => 'float',
        'purchase_date' => 'date',
        'current_price' => 'float',
        'current_value' => 'float',
        'cost_basis' => 'float',
        'dividend_yield' => 'float',
        'ocf_percent' => 'float',
    ];

    /**
     * Investment account relationship
     */
    public function investmentAccount(): BelongsTo
    {
        return $this->belongsTo(InvestmentAccount::class);
    }
}
