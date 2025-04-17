<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'isin',
        'quantity',
        'price',
        'type',
        'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'isin', 'isin')
            ->where('user_id', $this->user_id);
    }

}
