<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EstadoRecurso;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EstadoRecursoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EstadoRecurso');
    }

    public function view(AuthUser $authUser, EstadoRecurso $estadoRecurso): bool
    {
        return $authUser->can('View:EstadoRecurso');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EstadoRecurso');
    }

    public function update(AuthUser $authUser, EstadoRecurso $estadoRecurso): bool
    {
        return $authUser->can('Update:EstadoRecurso');
    }

    public function delete(AuthUser $authUser, EstadoRecurso $estadoRecurso): bool
    {
        return $authUser->can('Delete:EstadoRecurso');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EstadoRecurso');
    }

    public function restore(AuthUser $authUser, EstadoRecurso $estadoRecurso): bool
    {
        return $authUser->can('Restore:EstadoRecurso');
    }

    public function forceDelete(AuthUser $authUser, EstadoRecurso $estadoRecurso): bool
    {
        return $authUser->can('ForceDelete:EstadoRecurso');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EstadoRecurso');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EstadoRecurso');
    }

    public function replicate(AuthUser $authUser, EstadoRecurso $estadoRecurso): bool
    {
        return $authUser->can('Replicate:EstadoRecurso');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EstadoRecurso');
    }
}
