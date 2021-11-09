<?php

namespace App\Services;

use App\Models\User;

class AuthService
{

    public function login(User $user, array $requestData): array
    {
        $apiToken = $user->createToken($requestData['device_name'])->plainTextToken;

        $firstTimeLogin = false;
        if ($user->first_time_login === 1) {
            $firstTimeLogin = true;
        }

        if ($user->userUnitObject() === null) {
            return [
                'token' => $apiToken,
                'first_time_login' => $firstTimeLogin,
                'user_unit' => [],
                'user_role' => $user->userRole()->toArray()
            ];
        }

        return [
            'token' => $apiToken,
            'first_time_login' => $firstTimeLogin,
            'user_unit' => $user->userUnitObject()->toArray(),
            'user_role' => $user->userRole()->toArray()
        ];
    }

}