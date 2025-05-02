<?php

namespace App\Actions\Positions;

use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Collection;

class GetPositionsAction
{
    public function handle(User $user): Collection
    {
        return Position::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->get()
            ->sortByDesc(fn ($position) => $position->quantity * $position->average_price)
            ->values()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'name' => $position->name,
                    'isin' => $position->isin,
                    'quantity' => $position->quantity,
                    'average_price' => $position->average_price,
                    'current_price' => $position->current_price,
                ];
            });
    }
}
