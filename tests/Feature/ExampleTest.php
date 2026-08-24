<?php

test('root redirects to login', function () {
    $response = $this->get('/');

    $response->assertRedirect();
});
