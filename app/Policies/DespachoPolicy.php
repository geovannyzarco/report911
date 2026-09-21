<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Despacho;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DespachoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Despacho');
    }

    public function view(AuthUser $authUser, Despacho $despacho): bool
    {
        return $authUser->can('View:Despacho');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Despacho');
    }

    public function update(AuthUser $authUser, Despacho $despacho): bool
    {
        return $authUser->can('Update:Despacho');
    }

    public function delete(AuthUser $authUser, Despacho $despacho): bool
    {
        return $authUser->can('Delete:Despacho');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Despacho');
    }

    public function restore(AuthUser $authUser, Despacho $despacho): bool
    {
        return $authUser->can('Restore:Despacho');
    }

    public function forceDelete(AuthUser $authUser, Despacho $despacho): bool
    {
        return $authUser->can('ForceDelete:Despacho');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Despacho');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Despacho');
    }

    public function replicate(AuthUser $authUser, Despacho $despacho): bool
    {
        return $authUser->can('Replicate:Despacho');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Despacho');
    }
}
