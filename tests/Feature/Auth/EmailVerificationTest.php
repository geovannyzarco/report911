<?php

test('email verification screen can be rendered', function () {
    $response = $this->get('/verify-email');

    $response->assertNotFound();
})->skip('Email verification is disabled');

test('email can be verified', function () {
    $response = $this->get('/verify-email');

    $response->assertNotFound();
})->skip('Email verification is disabled');

test('email is not verified with invalid hash', function () {
    $response = $this->get('/verify-email');

    $response->assertNotFound();
})->skip('Email verification is disabled');
