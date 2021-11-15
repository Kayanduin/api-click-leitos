<?php

namespace App\Http\Controllers;

use App\Models\HealthUnit;
use App\Models\Role;
use App\Models\SamuUnit;
use App\Models\User;
use App\Models\UserContact;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Creates a new user in the database.
     * @param Request $request
     * @return Response
     */
    public function createUser(Request $request): Response
    {
        $requestData = $request->all();
        $validator = Validator::make(
            $requestData,
            [
                'name' => ['required'],
                'email' => ['required', 'email:rfc,dns', 'unique:users,email'],
                'cpf' => ['required', 'formato_cpf', 'cpf', 'unique:users,cpf'],
                'telephone_numbers' => ['required'],
                'telephone_numbers.*' => ['required', 'celular_com_ddd'],
                'user_role_id' => ['required', 'integer', 'exists:roles,id', 'gt:0'],
                'health_unit_id' => ['sometimes', 'required', 'exists:health_units,id', 'gt:0'],
                'samu_unit_id' => ['sometimes', 'required', 'exists:samu_units,id', 'gt:0']
            ],
            [
                'formato_cpf' => 'The field :attribute does not contain a valid CPF format.',
                'cpf' => 'The field :attribute does not contain a valid CPF.',
                'celular_com_ddd' => 'The field :attribute does not contains a telephone number in the' .
                    ' following format: (00) 00000-0000 or (00) 0000-0000'
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }

        $healthUnitAdministratorRole = (new Role())->where('type', 'health_unit_administrator')->first();

        if (
            array_key_exists('health_unit_id', $requestData) === false &&
            array_key_exists('samu_unit_id', $requestData) === false &&
            $requestData['user_role_id'] != $healthUnitAdministratorRole->id
        ) {
            return new Response(
                ['errors' => 'Error! The user could must have a Samu Unit id or a Health Unit id.'],
                400
            );
        }

        if (
            $request->user()->cannot(
                'create',
                [User::class, $requestData['user_role_id']]
            )
        ) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $userRole = (new Role())->find($requestData['user_role_id']);
        if ($userRole->type === 'health_unit_user') {
            if (
                array_key_exists('health_unit_id', $requestData) &&
                empty((new HealthUnit())->find($requestData['health_unit_id'])->toArray())
            ) {
                return new Response(['errors' => 'A Health Unit user must have a valid health_unit_id.'], 400);
            }
        }
        if ($userRole->type === 'samu_user') {
            if (
                array_key_exists('samu_unit_id', $requestData) &&
                empty((new SamuUnit())->find($requestData['samu_unit_id'])->toArray())
            ) {
                return new Response(['errors' => 'A Samu Unit user must have a valid samu_unit_id.'], 400);
            }
        }

        $userService = new UserService();

        if (!$userService->createUser($requestData)) {
            return new Response(['errors' => 'Error! The user could not be created.'], 500);
        }

        return new Response(['message' => 'Created user successfully!'], 201);
    }

    /**
     * Returns an associative array with all the users stored in the database.
     * @param Request $request
     * @param int $samuUnitId
     * @return Response
     */
    public function getAllSamuUnitUsersById(Request $request, int $samuUnitId): Response
    {
        $requestData = $request->all();
        $requestData['id'] = $samuUnitId;
        $validator = Validator::make(
            $requestData,
            [
                'id' => ['required', 'integer', 'exists:samu_units,id', 'gt:0'],
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }
        $healthUnitId = null;

        if (
            $request->user()->cannot(
                'viewAnyByUnit',
                [User::class, $healthUnitId, $samuUnitId]
            )
        ) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $userService = new UserService();
        $usersArray = $userService->getAllUsersFromSamuUnit($samuUnitId);
        if (is_array($usersArray)) {
            return new Response($usersArray, 200);
        }
        return new Response(['message' => 'There is no user registered.'], 200);
    }

    /**
     * Returns an associative array with all the users stored in the database.
     * @param Request $request
     * @param int $healthUnitId
     * @return Response
     */
    public function getAllHealthUnitUsersById(Request $request, int $healthUnitId): Response
    {
        $requestData = $request->all();
        $requestData['id'] = $healthUnitId;
        $validator = Validator::make(
            $requestData,
            [
                'id' => ['required_without:health_unit_id', 'integer', 'exists:samu_units,id', 'gt:0'],
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }
        $samuUnitId = null;

        if (
            $request->user()->cannot(
                'viewAnyByUnit',
                [User::class, $healthUnitId, $samuUnitId]
            )
        ) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $userService = new UserService();
        $usersArray = $userService->getAllUsersFromHealthUnit($healthUnitId);
        if (is_array($usersArray)) {
            return new Response($usersArray, 200);
        }
        return new Response(['message' => 'There is no user registered.'], 200);
    }


    /**
     * Returns an associative array with the user stored in the database that matches the user ID.
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function getUser(Request $request, $id): Response
    {
        $requestData['id'] = $id;
        $validator = Validator::make(
            $requestData,
            [
                'id' => ['required', 'exists:users,id']
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }

        if (is_null((new User())->find($id))) {
            return new Response(['errors' => 'This user doest not exists.'], 400);
        }

        if (
            $request->user()->cannot(
                'view',
                [User::class, $requestData['id']]
            )
        ) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $userService = new UserService();
        $userArray = $userService->getUser($id);

        return new Response($userArray, 200);
    }

    /**
     * Updates a user with the new values that were sent. The values are attached to a user stored in the
     * database that matches the user ID.
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function updateUser(Request $request, $id): Response
    {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $validator = Validator::make(
            $requestData,
            [
                'id' => ['required', 'exists:users,id'],
                'name' => ['sometimes', 'required'],
                'password' => ['sometimes', 'required'],
                'email' => ['sometimes', 'required', 'email:rfc,dns'],
                'cpf' => ['sometimes', 'required', 'formato_cpf', 'cpf', 'unique:users,cpf'],
                'telephone_numbers' => ['sometimes', 'required'],
                'telephone_numbers.*.id' => ['required', 'exists:user_contacts,id'],
                'telephone_numbers.*.telephone_number' => ['required', 'celular_com_ddd']
            ],
            [
                'formato_cpf' => 'The field :attribute does not contain a valid CPF format.',
                'cpf' => 'The field :attribute does not contain a valid CPF.',
                'celular_com_ddd' => 'The field :attribute does not contains a telephone number in the' .
                    ' following format: (00) 00000-0000 or (00) 0000-0000'
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }
        foreach ($requestData['telephone_numbers'] as $inputedContact) {
            $contact = UserContact::find($inputedContact['id']);
            if ($contact->user_id != $id) {
                $errorMessage = 'One of the updated contacts doesn\'t belongs to the inputed user.';
                return new Response(['errors' => $errorMessage], 400);
            }
        }

        if (is_null((new User())->find($id))) {
            return new Response(['errors' => 'This user doest not exists.'], 400);
        }

        $userToUpdate = (new User())->find($requestData['id']);
        if (
            $request->user()->cannot(
                'update',
                [User::class, $userToUpdate]
            )
        ) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $userService = new UserService();
        if ($userService->updateUser($requestData, $id)) {
            return new Response(['message' => 'Saved successfully!'], 200);
        }
        return new Response(['errors' => 'Error! The user could not be saved.'], 400);
    }

    /**
     * Deletes a user, and it's contacts from the database. The user to be deleted is find by the given ID.
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function deleteUser(Request $request, $id): Response
    {
        $requestData['id'] = $id;
        $validator = Validator::make(
            $requestData,
            [
                'id' => ['required', 'exists:users,id']
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }

        if (is_null((new User())->find($id))) {
            return new Response(['errors' => 'This user doest not exists.'], 400);
        }

        $userToDelete = (new User())->find($requestData['id']);
        if (
            $request->user()->cannot(
                'delete',
                [User::class, $userToDelete]
            )
        ) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $userService = new UserService();
        if ($userService->deleteUser($id)) {
            return new Response(['message' => 'Deleted successfully!'], 200);
        }
        return new Response(['errors' => 'Error! Failed to delete the contact.'], 500);
    }

    public function getUserRoles(Request $request): Response
    {
        if ($request->user()->cannot('viewRoles', User::class)) {
            return new Response(['errors' => 'Access denied.'], 403);
        }
        $userService = new UserService();
        $userRoles = $userService->getRoles();
        return new Response($userRoles, 200);
    }

    /**
     * Creates a new user in the database.
     * @param Request $request
     * @return Response
     */
    public function createFirstUser(Request $request): Response
    {
        $requestData = $request->all();
        $validator = Validator::make(
            $requestData,
            [
                'name' => ['required'],
                'email' => ['required', 'email:rfc,dns', 'unique:users,email'],
                'cpf' => ['required', 'formato_cpf', 'cpf', 'unique:users,cpf'],
                'telephone_numbers' => ['required'],
                'telephone_numbers.*' => ['required', 'celular_com_ddd'],
            ],
            [
                'formato_cpf' => 'The field :attribute does not contain a valid CPF format.',
                'cpf' => 'The field :attribute does not contain a valid CPF.',
                'celular_com_ddd' => 'The field :attribute does not contains a telephone number in the' .
                    ' following format: (00) 00000-0000 or (00) 0000-0000'
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }

        $requestData['user_role_id'] = 1;

        $userService = new UserService();

        if (!$userService->createUser($requestData)) {
            return new Response(['errors' => 'Error! The user could not be created.'], 500);
        }

        return new Response(['message' => 'Created user successfully!'], 201);
    }
}
