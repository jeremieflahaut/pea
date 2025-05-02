<?php

use App\Models\Transaction;
use App\Models\Position;
use App\Models\User;

it('has a user and a position relationship', function () {
    $user = User::factory()->create();

    $position = Position::factory()->create([
        'user_id' => $user->id,
        'isin' => 'FR0000000001',
    ]);

    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'isin' => $position->isin,
    ]);

    expect($transaction->user)->toBeInstanceOf(User::class)
        ->and($transaction->position)->toBeInstanceOf(Position::class);
});
