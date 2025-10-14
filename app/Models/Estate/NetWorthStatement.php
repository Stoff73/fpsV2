<?php

declare(strict_types=1);

namespace App\Models\Estate;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetWorthStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'statement_date',
        'total_assets',
        'total_liabilities',
        'net_worth',
    ];

    protected $casts = [
        'statement_date' => 'date',
        'total_assets' => 'float',
        'total_liabilities' => 'float',
        'net_worth' => 'float',
    ];

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
