<?php

use App\Models\Allocation;
use App\Models\Transaction;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('returns the list of transactions for the authenticated user', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Transaction::factory()->count(3)->create([
        'user_id' => $user->id,
    ]);

    $response = $this->getJson('/api/transactions');

    $response->assertOk();
    $response->assertJsonCount(3);
});

it('stores a transaction via the API when authenticated', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Allocation::factory()->create([
        'user_id' => $user->id,
        'isin' => 'FR0000123456',
        'name' => 'ETF World',
    ]);

    $response = $this->postJson('/api/transactions', [
        'isin' => 'FR0000123456',
        'type' => 'buy',
        'quantity' => 10,
        'price' => 123.45,
        'date' => now()->toISOString(),
    ]);

    $response->assertCreated();
    $response->assertJsonFragment([
        'isin' => 'FR0000123456',
        'quantity' => 10,
        'price' => 123.45,
    ]);
});

it('updates a transaction when the user is the owner', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'isin' => 'FR0000123456',
        'quantity' => 10,
        'price' => 100.1234,
        'type' => 'buy',
        'date' => '2025-04-17',
    ]);

    $payload = [
        'isin' => 'FR0000123456',
        'quantity' => 20,
        'price' => 105.5678,
        'type' => 'buy',
        'date' => '2025-05-01',
    ];

    $response = $this->putJson("/api/transactions/{$transaction->id}", $payload);

    $response->assertOk();
    $response->assertJsonFragment([
        'quantity' => 20,
        'price' => 105.5678,
        'date' => '2025-05-01',
    ]);
});

it('returns 403 if the user is not the owner of the transaction', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Sanctum::actingAs($user);

    $transaction = Transaction::factory()->create([
        'user_id' => $other->id,
    ]);

    $response = $this->putJson("/api/transactions/{$transaction->id}", [
        'isin' => 'FR0000123456',
        'quantity' => 99,
        'price' => 1,
        'type' => 'sell',
        'date' => '2025-01-01',
    ]);

    $response->assertForbidden();
});
