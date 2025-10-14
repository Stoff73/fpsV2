<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'goal_name',
        'target_amount',
        'current_saved',
        'target_date',
        'priority',
        'linked_account_id',
        'auto_transfer_amount',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_saved' => 'decimal:2',
        'target_date' => 'date',
        'auto_transfer_amount' => 'decimal:2',
    ];

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Linked savings account relationship
     */
    public function linkedAccount(): BelongsTo
    {
        return $this->belongsTo(SavingsAccount::class, 'linked_account_id');
    }
}
