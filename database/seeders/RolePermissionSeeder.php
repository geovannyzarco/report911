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

        // Los permisos ya fueron generados por Filament Shield.
        // Roles con permisos Shield (formato Pascal):
        // super_admin: acceso total
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo([
            'ViewAny:User', 'View:User', 'Create:User', 'Update:User', 'Delete:User',
            'DeleteAny:User', 'Restore:User', 'ForceDelete:User', 'ForceDeleteAny:User',
            'RestoreAny:User', 'Replicate:User', 'Reorder:User',
            'ViewAny:Role', 'View:Role', 'Create:Role', 'Update:Role', 'Delete:Role',
            'DeleteAny:Role', 'Restore:Role', 'ForceDelete:Role', 'ForceDeleteAny:Role',
            'RestoreAny:Role', 'Replicate:Role', 'Reorder:Role',
            'View:Dashboard', 'View:EventReport',
            'View:StatsOverview', 'View:IncidentAlertsWidget', 'View:DispatchStatusWidget',
            'View:FieldResourcesWidget', 'View:IncidentClassificationChart',
            'View:IncidentsByStatusChart', 'View:ActiveEventsWidget',
        ]);

        // jefe_despacho: puede gestionar usuarios y ver todo
        $jefeDespacho = Role::firstOrCreate(['name' => 'jefe_despacho']);
        $jefeDespacho->givePermissionTo([
            'ViewAny:User', 'View:User', 'Create:User', 'Update:User', 'Delete:User',
            'ViewAny:Role', 'View:Role',
            'View:Dashboard', 'View:EventReport',
            'View:StatsOverview', 'View:IncidentAlertsWidget', 'View:DispatchStatusWidget',
            'View:FieldResourcesWidget', 'View:IncidentClassificationChart',
            'View:IncidentsByStatusChart', 'View:ActiveEventsWidget',
        ]);

        // analista: solo lectura y reportes
        $analista = Role::firstOrCreate(['name' => 'analista']);
        $analista->givePermissionTo([
            'View:Dashboard', 'View:EventReport',
            'View:StatsOverview', 'View:IncidentAlertsWidget', 'View:DispatchStatusWidget',
            'View:FieldResourcesWidget', 'View:IncidentClassificationChart',
            'View:IncidentsByStatusChart', 'View:ActiveEventsWidget',
        ]);

        // auditor: solo lectura del dashboard
        $auditor = Role::firstOrCreate(['name' => 'auditor']);
        $auditor->givePermissionTo([
            'View:Dashboard', 'View:EventReport',
            'View:StatsOverview', 'View:IncidentAlertsWidget', 'View:DispatchStatusWidget',
            'View:IncidentClassificationChart', 'View:IncidentsByStatusChart',
        ]);

        // Asignar rol super_admin al usuario Geovanny
        $user = User::where('oni', 'ep00116')->first();
        if ($user) {
            $user->assignRole('super_admin');
        }
    }
}
