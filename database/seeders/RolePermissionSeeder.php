<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Los permisos se gestionan SOLO desde el panel de Filament Shield
        // (config/filament-shield.php), por lo que aqui solo se crean los roles.
        $roles = ['super_admin', 'jefe_despacho', 'analista', 'auditor', 'panel_user', 'despacho'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Asignar rol super_admin al usuario Geovanny
        $user = User::where('oni', 'ep00116')->first();
        if ($user) {
            $user->assignRole('super_admin');
        }
    }
}