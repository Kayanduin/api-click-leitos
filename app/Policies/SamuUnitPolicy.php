<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\UserUnit;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SamuUnitPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return Response
     */
    public function create(User $user): Response
    {
        $role = (new Role())->find($user->role_id);
        $userUnit = (new UserUnit())->find($user->id);
        if (!is_null($userUnit)) {
            return Response::deny();
        }
        if ($role->type === 'samu_administrator') {
            return Response::allow();
        }
        return Response::deny();
    }

    /**
     * @param User $user
     * @param int $samuUnitId
     * @return Response
     */
    public function view(User $user, int $samuUnitId): Response
    {
        $role = (new Role())->find($user->role_id);
        $userUnit = $user->userUnit();
        if ($role->type === 'samu_administrator' && $userUnit->samu_unit_id === $samuUnitId) {
            return Response::allow();
        }
        return Response::deny();
    }

    /**
     * @param User $user
     * @param int $samuUnitId
     * @return Response
     */
    public function update(User $user, int $samuUnitId): Response
    {
        $role = (new Role())->find($user->role_id);
        $userUnit = $user->userUnit();
        if ($role->type === 'samu_administrator' && $userUnit->samu_unit_id === $samuUnitId) {
            return Response::allow();
        }
        return Response::deny();
    }

    /**
     * @param User $user
     * @param int $samuUnitId
     * @return Response
     */
    public function delete(User $user, int $samuUnitId): Response
    {
        $role = (new Role())->find($user->role_id);
        $userUnit = $user->userUnit();
        if ($role->type === 'samu_administrator' && $userUnit->samu_unit_id === $samuUnitId) {
            return Response::allow();
        }
        return Response::deny();
    }
}
