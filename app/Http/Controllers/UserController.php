<?php

namespace App\Http\Controllers;

use App\Exceptions\Handler;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**
     * Creates a new user in the database.
     * @param Request $request
     * @return Response
     */
    public function createUser(Request $request): Response
    {
        try {
            $requestDataArray = $request->all();
            if (empty($requestDataArray)) {
                throw ValidationException::withMessages(['attributes' => ['All input attributes are empty.']]);
            }
            $this->userService->createUser($request->all());
        } catch (ValidationException | Exception $exception) {
            return $this->errorResponseGenerator($exception);
        }
        return new Response('Created user successfully!', 201);
    }

    /**
     * Returns an associative array with all the users stored in the database.
     * @return Response
     */
    public function getAllUsers(): Response
    {
        try {
            $this->userService = new UserService();
            $usersArray = $this->userService->getAllUsers();
        } catch (Exception $exception) {
            return $this->errorResponseGenerator($exception);
        }
        return new Response($usersArray, 200);
    }

    /**
     * Returns an associative array with the user stored in the database that matches the user ID.
     * @param $id
     * @return Response
     */
    public function getUser($id): Response
    {
        try {
            $this->userService = new UserService();
            $userArray = $this->userService->getUser($id);
        } catch (Exception $exception) {
            return $this->errorResponseGenerator($exception);
        }
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
        try {
            $requestDataArray = $request->all();
            if (empty($requestDataArray)) {
                throw ValidationException::withMessages(['attributes' => ['All input attributes are empty.']]);
            }
            $this->userService = new UserService();
            $this->userService->updateUser($requestDataArray, $id);
        } catch (ValidationException | Exception $exception) {
            return $this->errorResponseGenerator($exception);
        }
        return new Response('Saved successfully!', 200);
    }

    /**
     * Deletes a user, and it's contacts from the database. The user to be deleted is find by the given ID.
     * @param $id
     * @return Response
     */
    public function deleteUser($id): Response
    {
        try {
            $this->userService = new UserService();
            $this->userService->deleteUser($id);
        } catch (Exception $exception) {
            return $this->errorResponseGenerator($exception);
        }
        return new Response('User deleted successfully!', 200);
    }

    /**
     * Generates a response for an error based on the Exception that was thrown.
     * @param ValidationException|Exception $exception
     * @return Response
     */
    private function errorResponseGenerator(ValidationException|Exception $exception): Response
    {
        if ($exception instanceof ValidationException === false) {
            $errorMessage =
                'Internal server error! Please contact the system administrator and show the following message: '
                . PHP_EOL . $exception->getMessage()
                . PHP_EOL . 'With error code: ' . $exception->getCode();
            return new Response($errorMessage, 400);
        }
        $inputErrors = [];
        foreach ($exception->errors() as $key => $messageArray) {
            $errorMessage = '';
            foreach ($messageArray as $message) {
                $errorMessage .= ' ' . $message;
            }
            $inputErrors[$key . 'Error '] = $errorMessage;
        }

        $dataAndErrorMessageArray = $inputErrors;
        return new Response($dataAndErrorMessageArray, 400);
    }

//    public function register()
//    {
//        $this->handler->renderable(function (ValidationException $exception) {
//            return \response()->json([
//                'message' => $exception->getMessage()
//            ], 400);
//        });
//
//        $this->handler->renderable(function (Exception $exception) {
//            return \response()->json([
//                'message' => $exception->errors()
//            ], 400);
//        });
//    }
}
