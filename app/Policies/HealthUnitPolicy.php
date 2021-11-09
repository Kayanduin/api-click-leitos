<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\UserUnit;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class HealthUnitPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response
     */
    public function viewAny(User $user): Response
    {
        $role = (new Role())->find($user->role_id);
        if ($role->type === 'samu_administrator') {
            return Response::allow();
        }
        if ($role->type === 'samu_user') {
            return Response::allow();
        }
        return Response::deny('Access denied.');
    }

    /**
     * @param User $user
     * @param int $healthUnitId
     * @return Response
     */
    public function view(User $user, int $healthUnitId): Response
    {
        $role = (new Role())->find($user->role_id);
        $userUnit = $user->userUnit();
        if ($role->type === 'health_unit_administrator' && $userUnit->health_unit_id === $healthUnitId) {
            return Response::allow();
        }
        if ($role->type === 'health_unit_user' && $userUnit->health_unit_id === $healthUnitId) {
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

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response
     */
    public function create(User $user): Response
    {
        $role = (new Role())->find($user->role_id);
        if ($role->type === 'health_unit_administrator') {
            $userUnit = (new UserUnit())->where('user_id', $user->id)->first();
            if (is_null($userUnit) === false) {
                return Response::deny();
            }
            return Response::allow();
        }
        return Response::deny();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param int $healthUnitId
     * @return Response
     */
    public function update(User $user, int $healthUnitId): Response
    {
        $role = (new Role())->find($user->role_id);
        $userUnit = $user->userUnit();
        if ($role->type === 'health_unit_administrator' && $userUnit->health_unit_id === $healthUnitId) {
            return Response::allow();
        }
        if ($role->type === 'samu_administrator') {
            return Response::allow();
        }
        return Response::deny('Access denied.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @return Response
     */
    public function delete(User $user): Response
    {
        $role = (new Role())->find($user->role_id);
        if ($role->type === 'samu_administrator') {
            return Response::allow();
        }
        return Response::deny('Access denied.');
    }
}
