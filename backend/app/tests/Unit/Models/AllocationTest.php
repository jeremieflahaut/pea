<?php

use App\Models\Allocation;
use App\Models\User;

it('has a user relationship', function () {
    $user = User::factory()->create();

    $allocation = Allocation::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($allocation->user)->toBeInstanceOf(User::class);
});
