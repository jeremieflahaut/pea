<?php

use App\Models\Position;
use App\Models\Transaction;
use App\Models\User;

it('calculates the average price from buy transactions only', function () {
    $user = User::factory()->create();

    $position = Position::factory()->create([
        'user_id' => $user->id,
        'isin' => 'FR0000000001',
    ]);

    // Transactions "buy"
    Transaction::factory()->create([
        'user_id' => $user->id,
        'isin' => $position->isin,
        'type' => 'buy',
        'quantity' => 10,
        'price' => 20.00,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'isin' => $position->isin,
        'type' => 'buy',
        'quantity' => 5,
        'price' => 30.00,
    ]);

    // Une transaction "sell" ne doit pas influencer le average_price
    Transaction::factory()->create([
        'user_id' => $user->id,
        'isin' => $position->isin,
        'type' => 'sell',
        'quantity' => 3,
        'price' => 100.00,
    ]);

    $position->refresh(); // recharge pour les relations

    // (10×20 + 5×30) / (10+5) = (200 + 150) / 15 = 350 / 15 = 23.3333
    expect($position->average_price)->toBe(23.3333)
        ->and($position->user)->toBeInstanceOf(User::class);
});
