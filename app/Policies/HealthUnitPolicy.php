<?php

namespace App\Policies;

use App\Models\HealthUnit;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class HealthUnitPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param HealthUnit $healthUnit
     *
     */
    public function view(User $user, HealthUnit $healthUnit)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        $role = (new Role())->find($user->role_id);

        if ($role->type === 'health_unit_administrator') {
            return Response::allow();
        }

        return Response::deny('You can\'t create a new Health Unit.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @return Response
     */
    public function update(User $user)
    {
        $role = (new Role())->find($user->role_id);

        if ($role->type === 'health_unit_administrator') {
            return Response::allow();
        }

        return Response::deny('You can\'t update a Health Unit.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param HealthUnit $healthUnit
     */
    public function delete(User $user, HealthUnit $healthUnit)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param HealthUnit $healthUnit
     */
    public function restore(User $user, HealthUnit $healthUnit)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param HealthUnit $healthUnit
     */
    public function forceDelete(User $user, HealthUnit $healthUnit)
    {
        //
    }
}
