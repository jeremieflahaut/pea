<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'isin',
        'name',
        'quantity',
        'current_price'
    ];

    protected $appends = ['average_price'];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'isin', 'isin')
            ->where('user_id', $this->user_id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function averagePrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                $buys = $this->transactions()->where('type', 'buy')->get();

                $totalInvested = $buys->sum(fn($t) => $t->price * $t->quantity);
                $totalQuantity = $buys->sum('quantity');

                return $totalQuantity > 0
                    ? round($totalInvested / $totalQuantity, 4)
                    : 0;
            }
        );
    }

}
