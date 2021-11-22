<?php

namespace App\Services;

use App\Models\User;

class AuthService
{

    /**
     * Generates an authentication token and returns an array with the token and user information.
     * @param User $user
     * @param string $deviceName
     * @return array An array with the user information and authentication token.
     */
    public function generateLoginData(User $user, string $deviceName): array
    {
        $apiToken = $user
            ->createToken($deviceName)
            ->plainTextToken;

        $firstTimeLogin = false;
        if ($user->first_time_login === 1) {
            $firstTimeLogin = true;
        }

        if ($user->userUnitObject() === null) {
            return [
                'token' => $apiToken,
                'first_time_login' => $firstTimeLogin,
                'user_unit' => [],
                'user_role' => $user
                    ->userRole()
                    ->toArray()
            ];
        }

        return [
            'token' => $apiToken,
            'first_time_login' => $firstTimeLogin,
            'user_unit' => $user
                ->userUnitObject()
                ->toArray(),
            'user_role' => $user
                ->userRole()
                ->toArray()
        ];
    }
}
