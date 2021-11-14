<?php

namespace App\Http\Controllers;

use App\Models\HealthUnit;
use App\Models\HealthUnitContact;
use App\Models\UserUnit;
use App\Services\HealthUnitService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HealthUnitController extends Controller
{
    public function createHealthUnit(Request $request): Response
    {
        if ($request->user()->cannot('create', HealthUnit::class)) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $requestData = $request->all();

        $validator = Validator::make(
            $requestData,
            [
                'name' => ['required'],
                'address' => ['required'],
                'address_number' => ['required'],
                'district' => ['required'],
                'city_id' => ['required', 'integer', 'exists:cities,id'],
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
        } catch (ValidationException  $exception) {
            return new Response(['errors' => $exception->getMessage()], 500);
        }
        $healthUnitService = new HealthUnitService();
        $isHealthUnitCreated = $healthUnitService->createHealthUnit($validatedData);

        if (!$isHealthUnitCreated) {
            return new Response(['errors' => 'Error! The health unit could not be created.'], 500);
        }
        return new Response(['message' => 'Health unit created successfully!'], 201);
    }

    public function updateHealthUnit(Request $request, $healthUnitId): Response
    {
        $requestData = $request->all();
        $requestData['healthUnitId'] = $healthUnitId;
        $validator = Validator::make(
            $requestData,
            [
                'healthUnitId' => ['required', 'exists:health_units,id'],
                'name' => ['sometimes', 'required'],
                'address' => ['sometimes', 'required'],
                'address_number' => ['sometimes', 'required'],
                'district' => ['sometimes', 'required'],
                'city_id' => ['sometimes', 'required', 'integer', 'exists:cities,id'],
                'telephone_numbers' => ['sometimes', 'required'],
                'telephone_numbers.*.id' => ['required', 'exists:health_unit_contacts,id'],
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
                $contact = (new HealthUnitContact())->find($inputtedContact['id']);
                if ($contact->health_unit_id != $healthUnitId) {
                    $errorMessage = 'One of the updated contacts doesn\'t belongs to the inputted health unit.';
                    return new Response(['errors' => $errorMessage], 400);
                }
            }
        }
        try {
            $validatedData = $validator->validated();
        } catch (ValidationException $exception) {
            return new Response(['errors' => $exception->getMessage()], 500);
        }

        if ($request->user()->cannot('update', [HealthUnit::class, $healthUnitId])) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $healthUnitService = new HealthUnitService();
        $isHealthUnitUpdated = $healthUnitService->updateHealthUnit($validatedData);

        if (!$isHealthUnitUpdated) {
            return new Response(['errors' => 'Error! The health unit could not be updated.'], 500);
        }
        return new Response(['message' => 'Health unit updated successfully!'], 200);
    }

    public function getHealthUnit(Request $request, int $healthUnitId): Response
    {
        $requestData['healthUnitId'] = $healthUnitId;
        $validator = Validator::make(
            $requestData,
            [
                'healthUnitId' => ['required', 'exists:health_units,id']
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }

        if ($request->user()->cannot('view', [HealthUnit::class, $healthUnitId])) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $healthUnitService = new HealthUnitService();
        $healthUnit = $healthUnitService->getHealthUnit($healthUnitId);

        return new Response($healthUnit, 200);
    }

    public function getAllHealthUnits(Request $request): Response
    {
        if ($request->user()->cannot('viewAny', HealthUnit::class)) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $healthUnitService = new HealthUnitService();
        $healthUnits = $healthUnitService->getAllHealthUnits();
        if (is_array($healthUnits)) {
            return new Response($healthUnits, 200);
        }
        return new Response(['message' => 'There is no health unit registered.'], 200);
    }

    public function deleteHealthUnit(Request $request, int $healthUnitId): Response
    {
        if ($request->user()->cannot('delete', [HealthUnit::class, $healthUnitId])) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $requestData['healthUnitId'] = $healthUnitId;
        $validator = Validator::make(
            $requestData,
            [
                'healthUnitId' => ['required', 'exists:health_units,id']
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }
        $healthUnitService = new HealthUnitService();
        $deleteResult = $healthUnitService->deleteHealthUnit($healthUnitId);
        if ($deleteResult) {
            return new Response(['message' => 'Health unit deleted successfully!'], 200);
        }
        return new Response(['errors' => 'Error! Failed to delete the Health Unit.'], 500);
    }

    public function getAllHealthUnitsWithBeds(Request $request): Response
    {
        if ($request->user()->cannot('viewAny', HealthUnit::class)) {
            return new Response(['errors' => 'Access denied.'], 403);
        }
        $healthUnitService = new HealthUnitService();
        $healthUnits = $healthUnitService->getAllHealthUnitsWithBeds();

        return new Response($healthUnits, 200);
    }
}
