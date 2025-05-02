<?php

use App\Models\Position;
use App\Models\Transaction;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('returns only user positions with quantity > 0 ordered by invested amount desc', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // Position 1 : 5 * 100 = 500
    $position1 = Position::factory()->create([
        'user_id' => $user->id,
        'isin' => 'FR0000000001',
        'quantity' => 5,
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'isin' => $position1->isin,
        'type' => 'buy',
        'price' => 100,
        'quantity' => 5,
    ]);

    // Position 2 : 10 * 20 = 200
    $position2 = Position::factory()->create([
        'user_id' => $user->id,
        'isin' => 'FR0000000002',
        'quantity' => 10,
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'isin' => $position2->isin,
        'type' => 'buy',
        'price' => 20,
        'quantity' => 10,
    ]);

    // Position ignorée (quantity = 0)
    Position::factory()->create([
        'user_id' => $user->id,
        'isin' => 'FR0000000003',
        'quantity' => 0,
    ]);

    $response = $this->getJson('/api/positions');

    $response->assertOk();

    $data = $response->json();

    expect($data)->toHaveCount(2)
        ->and($data[0]['isin'])->toBe('FR0000000001') // 500 > 200
        ->and($data[1]['isin'])->toBe('FR0000000002');
});
