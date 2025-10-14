<?php

declare(strict_types=1);

namespace App\Models\Estate;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gift_date',
        'recipient',
        'gift_type',
        'gift_value',
        'status',
        'taper_relief_applicable',
    ];

    protected $casts = [
        'gift_date' => 'date',
        'gift_value' => 'float',
        'taper_relief_applicable' => 'boolean',
    ];

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
