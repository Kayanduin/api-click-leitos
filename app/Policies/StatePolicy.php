<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class StatePolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return Response
     */
    public function viewAny(User $user): Response
    {
        $role = (new Role())->find($user->role_id);
        if ($role->type === 'health_unit_administrator') {
            return Response::allow();
        }
        if ($role->type === 'health_unit_user') {
            return Response::allow();
        }
        if ($role->type === 'samu_administrator') {
            return Response::allow();
        }
        if ($role->type === 'samu_user') {
            return Response::allow();
        }
        return Response::deny('Access denied.');
    }
}
