<?php

use App\Models\User;
use App\Models\Allocation;
use App\Models\Transaction;
use App\Actions\Allocations\GetAllocationsAction;

it('computes current allocations with amount, percent and gap', function () {
    $user = User::factory()->create();

    // 2 allocations cibles
    Allocation::factory()->create([
        'user_id' => $user->id,
        'isin' => 'FR0000000001',
        'name' => 'ETF A',
        'type' => 'ETF',
        'target_percent' => 60,
    ]);

    Allocation::factory()->create([
        'user_id' => $user->id,
        'isin' => 'FR0000000002',
        'name' => 'ETF B',
        'type' => 'ETF',
        'target_percent' => 40,
    ]);

    // Transactions simulant une position réelle
    // ETF A : 5 x 10 = 50
    Transaction::factory()->create([
        'user_id' => $user->id,
        'isin' => 'FR0000000001',
        'type' => 'buy',
        'quantity' => 5,
        'price' => 10,
    ]);

    // ETF B : 2 x 15 = 30
    Transaction::factory()->create([
        'user_id' => $user->id,
        'isin' => 'FR0000000002',
        'type' => 'buy',
        'quantity' => 2,
        'price' => 15,
    ]);

    $allocations = app(GetAllocationsAction::class)->handle($user);

    // Vérifie ordre (60% d’abord)
    expect($allocations[0]['isin'])->toBe('FR0000000001');
    expect($allocations[1]['isin'])->toBe('FR0000000002');

    // Vérifie current_amount
    expect($allocations[0]['current_amount'])->toBe(50.0);
    expect($allocations[1]['current_amount'])->toBe(30.0);

    // Total = 80
    // ETF A => 50/80 = 62.5%
    expect($allocations[0]['current_percent'])->toBe(62.5);
    // ETF B => 30/80 = 37.5%
    expect($allocations[1]['current_percent'])->toBe(37.5);

    // Target amounts
    // ETF A: 60% × 80 = 48 ⇒ 50 > 48 → amount_to_add = 0
    expect($allocations[0]['amount_to_add'])->toBe(0.0);

    // ETF B: 40% × 80 = 32 ⇒ 30 < 32 → amount_to_add = 2
    expect($allocations[1]['amount_to_add'])->toBe(2.0);
});

