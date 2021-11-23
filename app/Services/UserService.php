<?php

namespace App\Services;

use App\Models\HealthUnit;
use App\Models\Role;
use App\Models\SamuUnit;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserUnit;
use Illuminate\Support\Facades\Hash;

const FIRST_USER_ID = 1;

class UserService
{
    /**
     * Persist the user and its contacts in the database.
     * @param string $name
     * @param string $email
     * @param string $cpf
     * @param int $userRoleId
     * @param array $telephoneNumbers
     * @param int|null $healthUnitId
     * @param int|null $samuUnitId
     * @return bool True on success, false on failure.
     */
    public function createUser(
        string $name,
        string $email,
        string $cpf,
        int $userRoleId,
        array $telephoneNumbers,
        int $healthUnitId = null,
        int $samuUnitId = null
    ): bool {
        $firstPassword = $this->generateUserFirstPassword();
        $userIdForCreatedByField = FIRST_USER_ID;
        if ((new User())->exists()) {
            /** @var User $user */
            $user = auth()->user();
            $userIdForCreatedByField = $user->id;
        }

        $user = new User([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($firstPassword, ['rounds' => 15]),
            'cpf' => $cpf,
            'first_time_login' => 1,
            'role_id' => $userRoleId,
            'deactivated_user' => false,
            'created_by' => $userIdForCreatedByField
        ]);
        $saveResult = $user->save();
        if ($saveResult === false) {
            return false;
        }
        $userId = $user->id;
        foreach ($telephoneNumbers as $telephoneNumber) {
            $contact = new UserContact([
                'user_id' => $userId,
                'telephone_number' => $telephoneNumber,
                'created_by' => $userIdForCreatedByField
            ]);
            $saveResult = $contact->save();
            if ($saveResult === false) {
                $user->delete();
                return false;
            }
        }

        if ($healthUnitId) {
            $userUnit = new UserUnit([
                'user_id' => $userId,
                'samu_unit_id' => null,
                'health_unit_id' => $healthUnitId,
                'created_by' => $userIdForCreatedByField
            ]);
            $saveResult = $userUnit->save();
            if ($saveResult === false) {
                /** @var UserContact $contact */
                foreach ($user->contacts() as $contact) {
                    $contact->delete();
                }
                $user->delete();
                return false;
            }
        }

        if ($samuUnitId) {
            $userUnit = new UserUnit([
                'user_id' => $userId,
                'samu_unit_id' => $samuUnitId,
                'health_unit_id' => null,
                'created_by' => $userIdForCreatedByField
            ]);
            $saveResult = $userUnit->save();
            if ($saveResult === false) {
                /** @var UserContact $contact */
                foreach ($user->contacts() as $contact) {
                    $contact->delete();
                }
                $user->delete();
                return false;
            }
        }

        $mailService = new MailService();
        $mailService->sendFirstPasswordMail($firstPassword, $user->email, $user->name);
        return true;
    }

    /**
     * Returns all users of a specific SAMU Unit that are registered in the database.
     * @param int $samuUnitId
     * @return array An array with data from found users in the database.
     */
    public function getAllUsersFromSamuUnit(int $samuUnitId): array
    {
        $resultArray = [];
        $allUsers = User::all();
        if (empty($allUsers->toArray())) {
            return [];
        }
        foreach ($allUsers as $user) {
            $userUnit = $user->userUnitObject();
            $userContacts = $user->contacts();
            $userRole = $user->userRole();

            $userArray = $user->toArray();
            $userArray['telephone_numbers'] = $userContacts->toArray();
            $userArray['user_role'] = $userRole->toArray();

            if ($userUnit instanceof SamuUnit && $userUnit->id === $samuUnitId) {
                $resultArray[] = $userArray;
            }
        }
        return $resultArray;
    }

    /**
     * Returns all users of a specific Health Unit that are registered in the database.
     * @param int $healthUnitId
     * @return array An array with data from found users in the database.
     */
    public function getAllUsersFromHealthUnit(int $healthUnitId): array
    {
        $resultArray = [];
        $allUsers = User::all();
        if (empty($allUsers->toArray())) {
            return [];
        }
        foreach ($allUsers as $user) {
            $userUnit = $user->userUnitObject();
            $userContacts = $user->contacts();
            $userRole = $user->userRole();

            $userArray = $user->toArray();
            $userArray['telephone_numbers'] = $userContacts->toArray();
            $userArray['user_role'] = $userRole->toArray();


            if ($userUnit instanceof HealthUnit && $userUnit->id === $healthUnitId) {
                $resultArray[] = $userArray;
            }
        }
        return $resultArray;
    }

    /**
     * Returns all Health Unit administrators created by the logged user.
     * @return array An array with data from all found users.
     */
    public function getAllHealthUnitAdminCreatedByLoggedUser(): array
    {
        /** @var User $loggedUser */
        $loggedUser = auth()->user();
        $resultArray = [];
        $allUsers = User::all();
        if (empty($allUsers->toArray())) {
            return [];
        }
        foreach ($allUsers as $user) {
            if ($user->created_by === $loggedUser->id) {
                $userRole = $user->userRole();

                if ($userRole->type === 'health_unit_administrator') {
                    $userContacts = $user->contacts();
                    $userArray = $user->toArray();
                    $userArray['telephone_numbers'] = $userContacts->toArray();
                    $userArray['user_role'] = $userRole->toArray();

                    $resultArray[] = $userArray;
                }
            }
        }
        return $resultArray;
    }

    /**
     * Returns all users registered in the database.
     * @return array An array with data from all users.
     */
    public function getAllUsers(): array
    {
        $resultArray = [];
        $allUsers = User::all();
        if (empty($allUsers->toArray())) {
            return [];
        }
        foreach ($allUsers as $user) {
            $userContacts = $user->contacts();
            $userRole = $user->userRole();

            $userArray = $user->toArray();
            $userArray['telephone_numbers'] = $userContacts->toArray();
            $userArray['user_role'] = $userRole->toArray();

            $resultArray[] = $userArray;
        }
        return $resultArray;
    }

    /**
     * Gets a specific user matching its given ID.
     * @param int $id
     * @return array An array with the data of the found user.
     */
    public function getUser(int $id): array
    {
        $user = (new User())->find($id);
        $resultArray = $user->toArray();
        $userContactsArray = (new UserContact())
            ->where('user_id', '=', $id)
            ->get()
            ->toArray();
        $resultArray['telephone_numbers'] = $userContactsArray;
        return $resultArray;
    }

    /**
     * Updates a user model by specified user id.
     * @param array $updatedUserData
     * @param int $userId
     * @return bool True on success, false on failure.
     */
    public function updateUser(array $updatedUserData, int $userId): bool
    {
        $user = (new User())->find($userId);
        foreach ($updatedUserData as $key => $value) {
            switch ($key) {
                case 'name':
                    $user->name = $value;
                    break;
                case 'email':
                    $user->email = $value;
                    break;
                case 'cpf':
                    $user->cpf = $value;
                    break;
                case 'telephone_numbers':
                    foreach ($value as $updatedContact) {
                        $contact = (new UserContact())->find($updatedContact['id']);
                        $contact->telephone_number = $updatedContact['telephone_number'];
                        $saveResult = $contact->save();
                        if ($saveResult === false) {
                            return false;
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        return $user->save();
    }

    /**
     * Deletes a specific user, and its contacts from the database.
     * @param int $userId
     * @return bool True on success, false on failure.
     */
    public function deleteUser(int $userId): bool
    {
        $user = (new User())->find($userId);
        $userContacts = (new UserContact())->where('user_id', '=', $userId)->get();
        foreach ($userContacts as $contact) {
            $deleteResult = $contact->delete();
            if ($deleteResult === false) {
                return false;
            }
        }
        $userUnit = (new UserUnit())->where('user_id', '=', $user->id);
        $deleteResult = $userUnit->delete();
        if ($deleteResult === false) {
            return false;
        }
        $user->tokens()->delete();
        $user->deactivated_user = true;
        $user->email = $user->id;
        $user->cpf = $user->id;
        return $user->save();
    }

    /**
     * Changes the user password.
     * @param string $newPassword
     * @return bool True on success, false on failure.
     */
    public function resetPassword(string $newPassword): bool
    {
        /** @var User $user */
        $user = auth()->user();
        $user->password = Hash::make($newPassword, ['rounds' => 15]);
        $user->first_time_login = 0;
        $isUserSaved = $user->save();
        if ($isUserSaved) {
            $mailService = new MailService();
            $mailService->sendResetPasswordMail($user->email);
            return true;
        }
        return false;
    }

    /**
     * Returns an array with all user roles.
     * @return array
     */
    public function getRoles(): array
    {
        return (new Role())
            ->get()
            ->toArray();
    }

    /**
     * Generates a password for the user login in the application for the first time. The password is a hashed token
     * without the metadata of the hash.
     * @return string A password.
     */
    private function generateUserFirstPassword(): string
    {
        $firstPasswordToken =
            'QyQ_%$ypZeLs54b4Vsz536&Ykc6Wp=vsLA#Z7=6dNwt*!VRXeVua#bm8R^zQV7hBLC8v&FrrEmF8xw8SLD&KRw%7+6$%%j95ExCk';
        $firstPassword = Hash::make($firstPasswordToken);
        return preg_replace('/^\$2y\$10\$/', '', $firstPassword);
    }
}
