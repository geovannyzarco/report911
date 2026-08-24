<?php

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Livewire\Livewire;

test('login screen can be rendered', function () {
    $response = $this->get('/monitoreo/login');

    $response->assertStatus(200);
});

test('users can authenticate using oni and password', function () {
    $user = User::factory()->create([
        'oni' => 'ep00116',
        'password' => 'password',
    ]);

    Livewire::test(Login::class)
        ->set('data.oni', 'ep00116')
        ->set('data.password', 'password')
        ->call('authenticate');

    $this->assertAuthenticated();
});

test('users can not authenticate with an unknown oni', function () {
    Livewire::test(Login::class)
        ->set('data.oni', '0000000000')
        ->set('data.password', 'password')
        ->call('authenticate');

    $this->assertGuest();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create([
        'oni' => 'ep00116',
    ]);

    Livewire::test(Login::class)
        ->set('data.oni', 'ep00116')
        ->set('data.password', 'wrong-password')
        ->call('authenticate');

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/monitoreo/logout');

    $this->assertGuest();
    $response->assertRedirect();
});
