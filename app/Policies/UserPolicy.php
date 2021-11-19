<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @param int $searchedUserId
     * @return Response
     */
    public function view(User $user, int $searchedUserId): Response
    {
        $loggedUserRole = (new Role())->find($user->role_id);
        $searchedUser = (new User())->find($searchedUserId);
        if (is_null($searchedUser->userUnit()) === false) {
            if (
                $loggedUserRole->type === 'health_unit_administrator' &&
                $searchedUser->userUnit()->health_unit_id === $user->userUnit()->health_unit_id
            ) {
                return Response::allow();
            }

            if (
                $loggedUserRole->type === 'samu_administrator' &&
                $searchedUser->userUnit()->samu_unit_id === $user->userUnit()->samu_unit_id
            ) {
                return Response::allow();
            }
        }

        if (
            $loggedUserRole->type === 'samu_administrator' &&
            $searchedUser->userRole()->type === 'health_unit_administrator' &&
            $searchedUser->created_by === $user->id
        ) {
            return Response::allow();
        }

        return Response::deny();
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @param int|null $healthUnitId
     * @param int|null $samuUnitId
     * @return Response
     */
    public function viewAnyByUnit(User $user, int|null $healthUnitId, int|null $samuUnitId): Response
    {
        $loggedUserRole = (new Role())->find($user->role_id);
        if ($healthUnitId !== null) {
            if ($loggedUserRole->type === 'health_unit_administrator') {
                return Response::allow();
            }
        }
        if ($samuUnitId !== null) {
            if ($loggedUserRole->type === 'samu_administrator') {
                return Response::allow();
            }
        }
        return Response::deny('Access denied.');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response
     */
    public function viewHealthUnitAdmin(User $user): Response
    {
        $loggedUserRole = (new Role())->find($user->role_id);
        if ($loggedUserRole->type === 'samu_administrator') {
            return Response::allow();
        }
        return Response::deny();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @param int $newUserRoleId
     * @return Response
     */
    public function create(User $user, int $newUserRoleId): Response
    {
        $newUserRole = (new Role())->find($newUserRoleId);
        $loggedUserRole = (new Role())->find($user->role_id);

        if ($loggedUserRole->type === 'samu_administrator') {
            if ($newUserRole->type === 'health_unit_administrator') {
                return Response::allow();
            }
            if ($newUserRole->type === 'samu_user') {
                return Response::allow();
            }
        }
        if ($loggedUserRole->type === 'health_unit_administrator') {
            if ($newUserRole->type === 'health_unit_user') {
                return Response::allow();
            }
        }
        return Response::deny('Access denied.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param User $userToUpdate
     * @return Response
     */
    public function update(User $user, User $userToUpdate): Response
    {
        $loggedUserRole = (new Role())->find($user->role_id);
        $healthUnitUserRole = (new Role())->where('type', '=', 'health_unit_user')->first();
        if (
            $loggedUserRole->type === 'health_unit_administrator' &&
            $userToUpdate->role_id === $healthUnitUserRole->id
        ) {
            return Response::allow();
        }
        if (
            $loggedUserRole->type === 'health_unit_administrator' &&
            $user->id === $userToUpdate->id
        ) {
            return Response::allow();
        }
        $samuUserRole = (new Role())->where('type', '=', 'samu_user')->first();
        if (
            $loggedUserRole->type === 'samu_administrator' &&
            $userToUpdate->role_id === $samuUserRole->id
        ) {
            return Response::allow();
        }
        if (
            $loggedUserRole->type === 'samu_administrator' &&
            $user->id === $userToUpdate->id
        ) {
            return Response::allow();
        }
        $healthUnitAdministratorUserRole = (new Role())->where('type', '=', 'health_unit_administrator')->first();
        if (
            $loggedUserRole->type === 'samu_administrator' &&
            $userToUpdate->role_id === $healthUnitAdministratorUserRole->id
        ) {
            return Response::allow();
        }
        if (
            $loggedUserRole->type === 'samu_user' &&
            $user->id === $userToUpdate->id
        ) {
            return Response::allow();
        }
        if (
            $loggedUserRole->type === 'health_unit_user' &&
            $user->id === $userToUpdate->id
        ) {
            return Response::allow();
        }
        return Response::deny('Access denied.');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param User $userToDelete
     * @return Response
     */
    public function delete(User $user, User $userToDelete): Response
    {
        $loggedUserRole = (new Role())->find($user->role_id);
        $healthUnitUserRole = (new Role())->where('type', '=', 'health_unit_user')->first();
        if (
            $loggedUserRole->type === 'health_unit_administrator'
            && $userToDelete->role_id === $healthUnitUserRole->id
        ) {
            return Response::allow();
        }
        $healthUnitAdministratorRole = (new Role())->where('type', '=', 'health_unit_administrator')->first();
        if (
            $loggedUserRole->type === 'samu_administrator' &&
            $userToDelete->role_id === $healthUnitAdministratorRole->id &&
            $userToDelete->created_by === $user->id
        ) {
            return Response::allow();
        }
        $samuUnitUserRole = (new Role())->where('type', 'samu_user')->first();
        if ($loggedUserRole->type === 'samu_administrator' && $userToDelete->role_id === $samuUnitUserRole->id) {
            return Response::allow();
        }
        if ($loggedUserRole->type === 'health_unit_administrator' && $user->id === $userToDelete->id) {
            return Response::allow();
        }
        if ($loggedUserRole->type === 'samu_administrator' && $user->id === $userToDelete->id) {
            return Response::allow();
        }

        return Response::deny('Access denied.');
    }

    /**
     * @param User $user
     * @return Response
     */
    public function viewRoles(User $user)
    {
        $loggedUserRole = (new Role())->find($user->role_id);
        if ($loggedUserRole->type === 'samu_administrator') {
            return Response::allow();
        }
        if ($loggedUserRole->type === 'health_unit_administrator') {
            return Response::allow();
        }
        return Response::deny('Access denied.');
    }
}
