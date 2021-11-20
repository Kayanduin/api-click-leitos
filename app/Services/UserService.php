<?php

namespace App\Services;

use App\Models\HealthUnit;
use App\Models\Role;
use App\Models\SamuUnit;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserUnit;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Validates the data, persist the user, and it's contacts in the database.
     * @param array $newUserData
     * @return string
     */
    public function createUser(array $newUserData): string
    {
        $token = 'QyQ_%$ypZeLs54b4Vsz536&Ykc6Wp=vsLA#Z7=6dNwt*!VRXeVua#bm8R^zQV7hBLC8v&FrrEmF8xw8SLD&KRw%7+6$%%j95ExCk';
        $firstPassword = Hash::make($token);
        $sanitizedFirstPassword = preg_replace('/^\$2y\$10\$/', '', $firstPassword);
        $createdById = 1;
        if ((new User())->exists()) {
            /** @var User $user */
            $user = auth()->user();
            $createdById = $user->id;
        }

        $user = new User([
            'name' => $newUserData['name'],
            'email' => $newUserData['email'],
            'password' => Hash::make($sanitizedFirstPassword, ['rounds' => 15]),
            'cpf' => $newUserData['cpf'],
            'first_time_login' => 1,
            'role_id' => $newUserData['user_role_id'],
            'deactivated_user' => false,
            'created_by' => $createdById
        ]);
        $saveResult = $user->save();
        if ($saveResult === false) {
            return false;
        }
        $userId = $user->getAttribute('id');
        foreach ($newUserData['telephone_numbers'] as $telephoneNumber) {
            $contact = new UserContact([
                'user_id' => $userId,
                'telephone_number' => $telephoneNumber,
                'created_by' => $createdById
            ]);
            $saveResult = $contact->save();
            if ($saveResult === false) {
                $user->delete();
                return false;
            }
        }

        if (array_key_exists('health_unit_id', $newUserData)) {
            $userUnit = new UserUnit([
                'user_id' => $userId,
                'samu_unit_id' => null,
                'health_unit_id' => $newUserData['health_unit_id'],
                'created_by' => $createdById
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

        if (array_key_exists('samu_unit_id', $newUserData)) {
            $userUnit = new UserUnit([
                'user_id' => $userId,
                'samu_unit_id' => $newUserData['samu_unit_id'],
                'health_unit_id' => null,
                'created_by' => $createdById
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
        $mailService->sendFirstPasswordMail($sanitizedFirstPassword, $user->email, $user->name);
        return true;
    }

    /**
     * Requests all users that are stored in the database.
     * @param int $samuUnitId
     * @return array
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

            if (!empty($userUnit)) {
                if ($userUnit->id === $samuUnitId && $userUnit instanceof SamuUnit) {
                    $resultArray[] = $userArray;
                }
            }
        }
        return $resultArray;
    }

    /**
     * Requests all users that are stored in the database.
     * @param int $healthUnitId
     * @return array
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

            if (!empty($userUnit)) {
                if ($userUnit->id === $healthUnitId && $userUnit instanceof HealthUnit) {
                    $resultArray[] = $userArray;
                }
            }
        }
        return $resultArray;
    }

    /**
     * Requests all users that are stored in the database.
     * @return array
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
                $userContacts = $user->contacts();
                $userRole = $user->userRole();

                $userArray = $user->toArray();
                $userArray['telephone_numbers'] = $userContacts->toArray();
                $userArray['user_role'] = $userRole->toArray();

                if ($userRole->type === 'health_unit_administrator') {
                    $resultArray[] = $userArray;
                }
            }
        }
        return $resultArray;
    }

    public function getAllUsers(): array|string
    {
        $resultArray = [];
        $allUsers = User::all();
        if (empty($allUsers->toArray())) {
            return 'There is no user registered.';
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
     * Gets a specific user matching it's given ID.
     * @param int $id
     * @return array
     */
    public function getUser(int $id): array
    {
        $user = User::find($id);
        $userArray = $user->toArray();
        $userContacts = UserContact::where('user_id', $id)->get();
        $userContactsArray = $userContacts->toArray();
        $userArray['telephone_numbers'] = $userContactsArray;
        return $userArray;
    }

    /**
     * Validates the data, gets a specific user matching it's given ID and update him.
     * @param array $updatedUserData
     * @param $userId
     * @return bool
     */
    public function updateUser(array $updatedUserData, $userId): bool
    {
        $user = User::find($userId);
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
     * Deletes a specific user, and it's contacts from the database.
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        $user = User::find($id);
        $userContacts = UserContact::where('user_id', $id)->get();
        foreach ($userContacts as $contact) {
            $deleteResult = $contact->delete();
            if ($deleteResult === false) {
                return false;
            }
        }
        $user->tokens()->delete();
        $user->deactivated_user = true;
        $user->email = $user->id;
        $user->cpf = $user->id;
        return $user->save();
    }

    /**
     * @param array $data
     * @return bool
     */
    public function resetPassword(array $data): bool
    {
        /** @var User $user */
        $user = auth()->user();
        $user->password = Hash::make($data['new_password'], ['rounds' => 15]);
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
     * @return array
     */
    public function getRoles(): array
    {
        return (new Role())->get()->toArray();
    }
}
