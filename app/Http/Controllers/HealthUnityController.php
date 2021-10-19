<?php

namespace App\Http\Controllers;

use App\Models\HealthUnityContact;
use App\Services\HealthUnityService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HealthUnityController extends Controller
{
    public function createHealthUnity(Request $request): Response
    {
        $requestData = $request->all();

        $validator = Validator::make(
            $requestData,
            [
                'name' => ['required'],
                'address' => ['required'],
                'address_number' => ['required'],
                'district' => ['required'],
                'city_id' => ['required', 'integer'],
                'telephone_numbers' => ['required'],
                'telephone_numbers.*' => ['required', 'celular_com_ddd']
            ],
            [
                'celular_com_ddd' => 'The field :attribute does not contains a telephone number in the' .
                    ' following format: (00) 00000-0000 or (00) 0000-0000'
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }
        try {
            $validatedData = $validator->validated();
        } catch (ValidationException $exception) {
            return new Response(['errors' => $exception->getMessage()], 500);
        }
        $healthUnityService = new HealthUnityService();
        $isHealthUnityCreated = $healthUnityService->createHealthUnity($validatedData);

        if (!$isHealthUnityCreated) {
            return new Response(['errors' => 'Error! The user could not be created.'], 500);
        }
        return new Response(['message' => 'Health unity created successfully!'], 201);
    }

    public function updateHealthUnity(Request $request, $healthUnityId): Response
    {
        $requestData = $request->all();
        $requestData['healthUnityId'] = $healthUnityId;
        $validator = Validator::make(
            $requestData,
            [
                'healthUnityId' => ['required', 'exists:health_unities,id'],
                'name' => ['sometimes', 'required'],
                'address' => ['sometimes', 'required'],
                'address_number' => ['sometimes', 'required'],
                'district' => ['sometimes', 'required'],
                'city_id' => ['sometimes', 'required', 'integer'],
                'telephone_numbers' => ['sometimes', 'required'],
                'telephone_numbers.*.id' => ['required', 'exists:health_unities_contacts,id'],
                'telephone_numbers.*.telephone_number' => ['required', 'celular_com_ddd']
            ],
            [
                'celular_com_ddd' => 'The field :attribute does not contains a telephone number in the' .
                    ' following format: (00) 00000-0000 or (00) 0000-0000'
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }
        if (array_key_exists('telephone_numbers', $requestData)) {
            foreach ($requestData['telephone_numbers'] as $inputtedContact) {
                $contact = (new HealthUnityContact())->find($inputtedContact['id']);
                if ($contact->health_unity_id != $healthUnityId) {
                    $errorMessage = 'One of the updated contacts doesn\'t belongs to the inputted health unity.';
                    return new Response(['errors' => $errorMessage], 400);
                }
            }
        }
        try {
            $validatedData = $validator->validated();
        } catch (ValidationException $exception) {
            return new Response(['errors' => $exception->getMessage()], 500);
        }
        $healthUnityService = new HealthUnityService();
        $isHealthUnityUpdated = $healthUnityService->updateHealthUnity($validatedData);

        if (!$isHealthUnityUpdated) {
            return new Response(['errors' => 'Error! The user could not be created.'], 500);
        }
        return new Response(['message' => 'Health unity updated successfully!'], 200);
    }

    public function getHealthUnity(int $healthUnityId): Response
    {
        $requestData['healthUnityId'] = $healthUnityId;
        $validator = Validator::make(
            $requestData,
            [
                'healthUnityId' => ['required', 'exists:health_unities,id']
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }
        $healthUnityService = new HealthUnityService();
        $userArray = $healthUnityService->getHealthUnity($healthUnityId);

        return new Response($userArray, 200);
    }

    public function getAllHealthUnities(): Response
    {
        $healthUnityService = new HealthUnityService();
        $healthUnities = $healthUnityService->getAllHealthUnities();
        if (is_array($healthUnities)) {
            return new Response($healthUnities, 200);
        }
        return new Response(['message' => 'There is no health unity registered.'], 200);
    }

    public function deleteHealthUnity(int $healthUnityId): Response
    {
        $requestData['healthUnityId'] = $healthUnityId;
        $validator = Validator::make(
            $requestData,
            [
                'healthUnityId' => ['required', 'exists:health_unities,id']
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }
        $healthUnityService = new HealthUnityService();
        $deleteResult = $healthUnityService->deleteHealthUnity($healthUnityId);
        if ($deleteResult) {
            return new Response(['message' => 'Health unity deleted successfully!'], 200);
        }
        return new Response(['errors' => 'Error! Failed to delete the Health Unity.'], 500);
    }
}
