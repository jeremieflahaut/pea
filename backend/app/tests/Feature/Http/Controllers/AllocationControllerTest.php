<?php

use App\Models\Allocation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

it('returns only allocations of the authenticated user', function () {
    Allocation::factory()->count(2)->create(['user_id' => $this->user->id]);
    Allocation::factory()->create();

    $response = $this->getJson('/api/allocations');

    $response->assertOk();
    expect($response->json())->toHaveCount(2);
});

it('stores a new allocation for the authenticated user', function () {
    $payload = [
        'isin' => 'FR0000000001',
        'name' => 'Amundi MSCI World',
        'ticker' => 'CW8',
        'type' => 'ETF',
        'target_percent' => 20,
    ];

    $response =  $this->postJson('/api/allocations', $payload);

    $response->assertCreated();
    $response->assertJsonFragment(['isin' => 'FR0000000001']);
    expect(Allocation::where('user_id', $this->user->id)->count())->toBe(1);
});

it('updates an allocation owned by the user', function () {
    $allocation = Allocation::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Old Name',
    ]);

    $payload = [
        'name' => 'New Name',
        'type' => 'Action',
        'target_percent' => 30,
    ];

    $response =  $this->putJson("/api/allocations/{$allocation->id}", $payload);

    $response->assertOk();
    expect($response->json('name'))->toBe('New Name');
    $allocation->refresh();
    expect($allocation->name)->toBe('New Name');
});

it('forbids updating an allocation not owned by the user', function () {
    $other = User::factory()->create();
    $allocation = Allocation::factory()->create(['user_id' => $other->id]);

    $payload = [
        'name' => 'Hacked Name',
        'type' => 'ETF',
        'target_percent' => 10,
    ];

    $response =  $this->putJson("/api/allocations/{$allocation->id}", $payload);

    $response->assertForbidden();
});
