<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('logs in a user with correct credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJson(['message' => 'Connecté']);

    expect(Auth::check())->toBeTrue();
});

it('fails to log in with invalid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');

    expect(Auth::check())->toBeFalse();
});

it('logs out the user and invalidates session', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    expect(Auth::check())->toBeTrue();

    $response = $this->postJson('api/logout');

    $response->assertOk()
        ->assertJson(['message' => 'Déconnecté']);

    expect(Auth::check())->toBeFalse();
});

