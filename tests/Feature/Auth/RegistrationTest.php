<?php

use App\Models\User;
use Livewire\Volt\Volt;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = Volt::test('auth.register')
        ->set('name', 'Test User')
        ->set('oni', '1234567890')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    expect(User::where('oni', '1234567890')->exists())->toBeTrue();
});

test('registration fails with a duplicated oni', function () {
    User::factory()->create(['oni' => '1234567890']);

    $response = Volt::test('auth.register')
        ->set('name', 'Test User')
        ->set('oni', '1234567890')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    $response->assertHasErrors(['oni']);

    $this->assertGuest();
});
