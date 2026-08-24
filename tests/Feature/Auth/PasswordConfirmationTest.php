<?php

test('confirm password screen can be rendered', function () {
    $response = $this->get('/confirm-password');

    $response->assertNotFound();
})->skip('Password confirmation is handled by Filament');
