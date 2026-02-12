<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('register creates a user and returns token', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'User registered successfully.')
        ->assertJsonPath('data.user.email', 'jane@example.com')
        ->assertJsonPath('data.token_type', 'Bearer');

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
    ]);
});

test('login returns token for valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'jane@example.com',
        'password' => 'password123',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Login successful.')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.token_type', 'Bearer');
});

test('login fails for invalid credentials', function () {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'jane@example.com',
        'password' => 'wrong-password',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Invalid credentials.');
});
