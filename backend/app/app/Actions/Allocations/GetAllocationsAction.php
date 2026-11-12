<?php

namespace App\Actions\Allocations;

use App\Models\Allocation;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Collection;

class GetAllocationsAction
{
    public function handle(User $user): Collection
    {
        $targets = Allocation::where('user_id', $user->id)->get();
        // Valorisation au cours actuel (mis à jour par app:get-positions-price),
        // pas au prix de la dernière transaction.
        $positions = Position::where('user_id', $user->id)->get()
            ->keyBy('isin')
            ->map(fn (Position $position) => [
                'isin' => $position->isin,
                'quantity' => $position->quantity,
                'current_price' => $position->current_price,
                'current_amount' => round($position->quantity * $position->current_price, 2),
            ]);

        $allocations = $targets->map(function ($target) use ($positions) {
            $position = $positions[$target->isin] ?? [
                'quantity' => 0,
                'current_price' => 0,
                'current_amount' => 0,
            ];

            return [
                'id' => $target->id,
                'label' => $target->name,
                'isin' => $target->isin,
                'type' => $target->type,
                'target_percent' => $target->target_percent,
                'current_amount' => $position['current_amount'],
            ];
        });

        $total = $allocations->sum('current_amount');

        $allocations = $allocations->map(function ($allocation) use ($total) {
            $target_amount = round($allocation['target_percent'] / 100 * $total, 2);
            $amount_to_add = round($target_amount - $allocation['current_amount'], 2);
            $current_percent = $total > 0 ? round($allocation['current_amount'] / $total * 100, 2) : 0;

            return [
                ...$allocation,
                'current_percent' => $current_percent,
                'amount_to_add' => (float) max($amount_to_add, 0),
            ];
        });

        return $allocations->sortByDesc('target_percent')->values();
    }
}
