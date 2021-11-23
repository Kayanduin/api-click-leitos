<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): Response
    {
        $requestData = $request->all();
        $validator = Validator::make(
            $requestData,
            [
                'email' => ['required', 'email:rfc,dns'],
                'password' => ['required'],
                'device_name' => ['required'],
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 401);
        }

        $user = (new User())->where('email', '=', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return new Response(['errors' => 'The provided credentials are incorrect.'], 401);
        }

        $authService = new AuthService();
        $loginData = $authService->generateLoginData($user, $requestData['device_name']);

        return new Response($loginData, 200);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function resetPassword(Request $request): Response
    {
        $requestData = $request->all();
        $validator = Validator::make(
            $requestData,
            [
                'new_password' => ['required'],
                'confirm_new_password' => ['required', 'same:new_password']
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 401);
        }
        try {
            $validatedData = $validator->validated();
        } catch (ValidationException $exception) {
            return new Response(['errors' => $exception->getMessage()], 500);
        }

        $userService = new UserService();
        $isPasswordReseted = $userService->resetPassword($validatedData['new_password']);

        if (!$isPasswordReseted) {
            return new Response(['errors' => 'Could not reset the password.'], 400);
        }
        return new Response(['message' => 'Password reseted successfully.'], 200);
    }
}
