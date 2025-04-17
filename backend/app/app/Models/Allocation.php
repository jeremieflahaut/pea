<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Allocation extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'isin',
        'type',
        'target_percent'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
