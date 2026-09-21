<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Agente;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AgentePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Agente');
    }

    public function view(AuthUser $authUser, Agente $agente): bool
    {
        return $authUser->can('View:Agente');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Agente');
    }

    public function update(AuthUser $authUser, Agente $agente): bool
    {
        return $authUser->can('Update:Agente');
    }

    public function delete(AuthUser $authUser, Agente $agente): bool
    {
        return $authUser->can('Delete:Agente');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Agente');
    }

    public function restore(AuthUser $authUser, Agente $agente): bool
    {
        return $authUser->can('Restore:Agente');
    }

    public function forceDelete(AuthUser $authUser, Agente $agente): bool
    {
        return $authUser->can('ForceDelete:Agente');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Agente');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Agente');
    }

    public function replicate(AuthUser $authUser, Agente $agente): bool
    {
        return $authUser->can('Replicate:Agente');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Agente');
    }
}
