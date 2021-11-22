<?php

namespace App\Policies;

use App\Models\Bed;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class BedPolicy
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

    public function viewById(User $user, int $bedId)
    {
        $role = (new Role())->find($user->role_id);
        $bed = (new Bed())->find($bedId);
        $userUnit = $user->userUnit();
        if ($role->type === 'health_unit_administrator' && $userUnit->health_unit_id === $bed->health_unit_id) {
            return Response::allow();
        }
        if ($role->type === 'health_unit_user' && $userUnit->health_unit_id === $bed->health_unit_id) {
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

    public function viewByHealthUnitId(User $user, int $healthUnitId)
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
     * @param int $healthUnitId
     * @return Response
     */
    public function create(User $user, int $healthUnitId): Response
    {
        $role = (new Role())->find($user->role_id);
        $userUnit = $user->userUnit();
        if ($role->type === 'health_unit_administrator' && $userUnit->health_unit_id === $healthUnitId) {
            return Response::allow();
        }
        return Response::deny('Access denied.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Bed $bed
     * @return Response
     */
    public function update(User $user, Bed $bed): Response
    {
        $role = (new Role())->find($user->role_id);
        $userUnit = $user->userUnit();
        if ($role->type === 'health_unit_administrator' && $userUnit->health_unit_id === $bed->health_unit_id) {
            return Response::allow();
        }
        if ($role->type === 'health_unit_user' && $userUnit->health_unit_id === $bed->health_unit_id) {
            return Response::allow();
        }
        return Response::deny('Access denied.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Bed $bed
     * @return Response
     */
    public function delete(User $user, Bed $bed): Response
    {
        $role = (new Role())->find($user->role_id);
        $userUnit = $user->userUnit();
        if ($role->type === 'health_unit_administrator' && $userUnit->health_unit_id === $bed->health_unit_id) {
            return Response::allow();
        }
        return Response::deny('Access denied.');
    }

    public function canAlterFreeBedNumber(User $user, Bed $bed): Response
    {
        $role = (new Role())->find($user->role_id);
        $userUnit = $user->userUnit();
        if ($role->type === 'health_unit_administrator' && $userUnit->health_unit_id === $bed->health_unit_id) {
            return Response::allow();
        }
        if ($role->type === 'health_unit_user' && $userUnit->health_unit_id === $bed->health_unit_id) {
            return Response::allow();
        }
        return Response::deny('Access denied.');
    }

    public function viewBedType(User $user)
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
