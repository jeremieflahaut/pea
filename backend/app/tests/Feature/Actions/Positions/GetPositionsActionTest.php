<?php

use App\Actions\Positions\GetPositionsAction;
use App\Models\Position;
use App\Models\Transaction;
use App\Models\User;

it('returns non-zero positions sorted by invested amount', function () {
    $user = User::factory()->create();

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

    Position::factory()->create([
        'user_id' => $user->id,
        'isin' => 'FR0000000003',
        'quantity' => 0,
    ]);

    $positions = app()->make(GetPositionsAction::class)->handle($user);

    expect($positions)->toHaveCount(2)
        ->and($positions->first()['isin'])->toBe('FR0000000001')
        ->and($positions->last()['isin'])->toBe('FR0000000002');
});
