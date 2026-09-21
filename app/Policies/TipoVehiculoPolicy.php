<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TipoVehiculo;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TipoVehiculoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TipoVehiculo');
    }

    public function view(AuthUser $authUser, TipoVehiculo $tipoVehiculo): bool
    {
        return $authUser->can('View:TipoVehiculo');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TipoVehiculo');
    }

    public function update(AuthUser $authUser, TipoVehiculo $tipoVehiculo): bool
    {
        return $authUser->can('Update:TipoVehiculo');
    }

    public function delete(AuthUser $authUser, TipoVehiculo $tipoVehiculo): bool
    {
        return $authUser->can('Delete:TipoVehiculo');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TipoVehiculo');
    }

    public function restore(AuthUser $authUser, TipoVehiculo $tipoVehiculo): bool
    {
        return $authUser->can('Restore:TipoVehiculo');
    }

    public function forceDelete(AuthUser $authUser, TipoVehiculo $tipoVehiculo): bool
    {
        return $authUser->can('ForceDelete:TipoVehiculo');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TipoVehiculo');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TipoVehiculo');
    }

    public function replicate(AuthUser $authUser, TipoVehiculo $tipoVehiculo): bool
    {
        return $authUser->can('Replicate:TipoVehiculo');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TipoVehiculo');
    }
}
