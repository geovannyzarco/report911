<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

test('database seeder creates the default user', function () {
    $this->seed(DatabaseSeeder::class);

    $user = User::where('oni', 'ep00116')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Geovanny')
        ->and($user->email)->toBe('ep00116@pnc.gob.sv')
        ->and($user->email_verified_at)->not->toBeNull()
        ->and(Hash::check('100504', $user->password))->toBeTrue();
});
