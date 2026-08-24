<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        Permission::create(['name' => 'ver dashboard']);
        Permission::create(['name' => 'ver reportes']);
        Permission::create(['name' => 'generar reportes']);
        Permission::create(['name' => 'ver monitoreo']);
        Permission::create(['name' => 'administrar usuarios']);
        Permission::create(['name' => 'editar usuarios']);
        Permission::create(['name' => 'eliminar usuarios']);

        // Crear roles y asignar permisos
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'ver dashboard',
            'ver reportes',
            'generar reportes',
            'ver monitoreo',
            'administrar usuarios',
            'editar usuarios',
            'eliminar usuarios',
        ]);

        $operador = Role::create(['name' => 'operador']);
        $operador->givePermissionTo([
            'ver dashboard',
            'ver reportes',
            'ver monitoreo',
        ]);

        $supervisor = Role::create(['name' => 'supervisor']);
        $supervisor->givePermissionTo([
            'ver dashboard',
            'ver reportes',
            'generar reportes',
            'ver monitoreo',
            'editar usuarios',
        ]);

        // Asignar rol admin al usuario Geovanny
        $user = User::where('oni', 'ep00116')->first();
        if ($user) {
            $user->assignRole('admin');
        }
    }
}
