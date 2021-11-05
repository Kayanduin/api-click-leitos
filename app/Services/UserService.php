<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserContact;
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
            'role_id' => 1,
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
        $mailService = new MailService();
        $mailService->sendFirstPasswordMail($sanitizedFirstPassword, $user->email);
        return true;
    }

    /**
     * Requests all users that are stored in the database.
     * @return array|string
     */
    public function getAllUsers(): array|string
    {
        $resultArray = [];
        $allUsers = User::all();
        if (empty($allUsers->toArray())) {
            return 'There is no user registered.';
        }
        foreach ($allUsers as $userArrayKey => $user) {
            $resultArray[$userArrayKey] = $user->toArray();
            $userId = $user->getAttribute('id');
            $userContacts = UserContact::where('user_id', $userId)->get();
            $userContactsArray = $userContacts->toArray();
            $resultArray[$userArrayKey]['telephone_numbers'] = $userContactsArray;
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
        return $user->delete();
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
}
