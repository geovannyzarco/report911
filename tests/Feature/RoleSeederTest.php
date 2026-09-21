<?php

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('crea los roles base del sistema', function () {
    $this->seed(RolePermissionSeeder::class);

    $roles = Role::pluck('name')->map(fn ($name) => $name)->flip();

    expect($roles)->toHaveKeys([
        'super_admin',
        'jefe_despacho',
        'analista',
        'auditor',
        'panel_user',
        'despacho',
    ]);
});
