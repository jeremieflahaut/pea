<?php

namespace App\Actions;

use App\Models\Allocation;
use App\Models\Position;
use App\Models\Transaction;
use App\Models\User;

class StoreTransactionAction
{
    public function handle(User $user, array $data): Transaction
    {
        $transaction = $user->transactions()->create($data);

        $allocation = Allocation::where('user_id', $user->id)
            ->where('isin', data_get($data, 'isin'))
            ->first();

        $position = Position::firstOrNew([
            'user_id' => $user->id,
            'isin' => data_get($data, 'isin'),
        ]);

        if (! $position->exists) {
            $position->name = optional($allocation)->name ?? data_get($data, 'isin');
        }

        if (data_get($data, 'type') === 'buy') {
            $position->quantity = ($position->quantity ?? 0) + data_get($data, 'quantity');
        } elseif (data_get($data, 'type') === 'sell') {
            $position->quantity = max(0, ($position->quantity ?? 0) - data_get($data, 'quantity'));
        }

        $position->save();

        return $transaction;

    }
}
