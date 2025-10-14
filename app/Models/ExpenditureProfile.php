<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenditureProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'monthly_housing',
        'monthly_utilities',
        'monthly_food',
        'monthly_transport',
        'monthly_insurance',
        'monthly_loans',
        'monthly_discretionary',
        'total_monthly_expenditure',
    ];

    protected $casts = [
        'monthly_housing' => 'decimal:2',
        'monthly_utilities' => 'decimal:2',
        'monthly_food' => 'decimal:2',
        'monthly_transport' => 'decimal:2',
        'monthly_insurance' => 'decimal:2',
        'monthly_loans' => 'decimal:2',
        'monthly_discretionary' => 'decimal:2',
        'total_monthly_expenditure' => 'decimal:2',
    ];

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
