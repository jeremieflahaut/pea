<?php

use App\Actions\StoreTransactionAction;
use App\Models\Allocation;
use App\Models\Position;
use App\Models\Transaction;
use App\Models\User;

use Illuminate\Support\Carbon;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->action = app()->make(StoreTransactionAction::class);
});

it('can create a transaction and a new position', function () {
    Allocation::factory()->create([
        'user_id' => $this->user->id,
        'isin' => 'FR0000123456',
        'name' => 'Amundi PEA MSCI World',
    ]);

    $data = [
        'isin' => 'FR0000123456',
        'type' => 'buy',
        'quantity' => 10,
        'price' => 100,
        'executed_at' => Carbon::now()->toDateTimeString(),
    ];

    $transaction = $this->action->handle($this->user, $data);

    expect($transaction)->toBeInstanceOf(Transaction::class);

    assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'isin' => 'FR0000123456',
        'quantity' => 10,
        'price' => 100,
    ]);

    assertDatabaseHas('positions', [
        'user_id' => $this->user->id,
        'isin' => 'FR0000123456',
        'quantity' => 10,
        'name' => 'Amundi PEA MSCI World',
    ]);
});

it('can update an existing position when buying again', function () {
    Position::factory()->create([
        'user_id' => $this->user->id,
        'isin' => 'FR0000123456',
        'quantity' => 5,
        'name' => 'Position Existante',
    ]);

    $data = [
        'isin' => 'FR0000123456',
        'type' => 'buy',
        'quantity' => 7,
        'price' => 150,
        'executed_at' => Carbon::now()->toDateTimeString(),
    ];

    $this->action->handle($this->user, $data);

    $position = Position::where('user_id', $this->user->id)->where('isin', 'FR0000123456')->first();

    expect($position)->not()->toBeNull()
        ->and($position->quantity)->toBe(12); // 5 + 7
});

it('can decrease the position when selling', function () {
    Position::factory()->create([
        'user_id' => $this->user->id,
        'isin' => 'FR0000123456',
        'quantity' => 10,
        'name' => 'Position Existante',
    ]);

    $data = [
        'isin' => 'FR0000123456',
        'type' => 'sell',
        'quantity' => 4,
        'price' => 90,
        'executed_at' => Carbon::now()->toDateTimeString(),
    ];

    $this->action->handle($this->user, $data);

    $position = Position::where('user_id', $this->user->id)->where('isin', 'FR0000123456')->first();

    expect($position)->not()->toBeNull()
        ->and($position->quantity)->toBe(6); // 10 - 4
});
