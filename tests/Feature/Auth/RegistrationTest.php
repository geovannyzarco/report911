<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
})->skip('Registration is disabled');

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'oni' => '1234567890',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertNotFound();
})->skip('Registration is disabled');

test('registration fails with a duplicated oni', function () {
    User::factory()->create(['oni' => '1234567890']);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'oni' => '1234567890',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertNotFound();
})->skip('Registration is disabled');
