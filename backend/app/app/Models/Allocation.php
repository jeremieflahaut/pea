<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Allocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'isin',
        'ticker',
        'type',
        'target_percent'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
