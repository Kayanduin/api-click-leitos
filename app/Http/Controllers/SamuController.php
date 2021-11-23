<?php

namespace App\Http\Controllers;

use App\Models\SamuUnit;
use App\Services\SamuService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SamuController extends Controller
{
    public function create(Request $request): Response
    {
        if ($request->user()->cannot('create', SamuUnit::class)) {
            return new Response(['errors' => 'Access denied.'], 403);
        }
        $validator = Validator::make(
            $request->toArray(),
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
        } catch (ValidationException $exception) {
            return new Response(['errors' => $exception->getMessage()], 500);
        }

        $samuService = new SamuService();
        $isSamuUnitCreated = $samuService->createSamuUnit(
            $validatedData['name'],
            $validatedData['address'],
            $validatedData['address_number'],
            $validatedData['district'],
            $validatedData['city_id'],
            $validatedData['telephone_numbers']
        );

        if (!$isSamuUnitCreated) {
            return new Response(['errors' => 'Error! The samu unit could not be created.'], 500);
        }
        return new Response(['message' => 'Samu unit created successfully!'], 201);
    }

    public function view(Request $request, int $id): Response
    {
        $requestData['id'] = $id;
        $validator = Validator::make(
            $requestData,
            [
                'id' => ['required', 'exists:samu_units,id']
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

        if ($request->user()->cannot('view', [SamuUnit::class, $id])) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $samuService = new SamuService();
        $samuUnit = $samuService->getSamuUnit($validatedData['id']);
        return new Response($samuUnit, 200);
    }

    public function update(Request $request, int $id): Response
    {
        $requestData = $request->toArray();
        $requestData['samu_unit_id'] = $id;
        $validator = Validator::make(
            $requestData,
            [
                'samu_unit_id' => ['required', 'exists:samu_units,id', 'gt:0'],
                'name' => ['sometimes', 'required'],
                'address' => ['sometimes', 'required'],
                'address_number' => ['sometimes', 'required'],
                'district' => ['sometimes', 'required'],
                'city_id' => ['sometimes', 'required', 'integer', 'exists:cities,id'],
                'telephone_numbers' => ['sometimes', 'required'],
                'telephone_numbers.*.id' => ['required', 'exists:samu_unit_contacts,id'],
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
        try {
            $validatedData = $validator->validated();
        } catch (ValidationException $exception) {
            return new Response(['errors' => $exception->getMessage()], 500);
        }

        if ($request->user()->cannot('update', [SamuUnit::class, $id])) {
            return new Response(['errors' => 'Access denied.'], 403);
        }
        $samuService = new SamuService();
        $isSamuUnitUpdated = $samuService->updateSamuUnit($validatedData['samu_unit_id'], $validatedData);

        if (!$isSamuUnitUpdated) {
            return new Response(['errors' => 'Error! The samu unit could not be updated.'], 500);
        }
        return new Response(['message' => 'Samu unit updated successfully!'], 201);
    }

    public function delete(Request $request, int $id): Response
    {
        $requestData['samu_unit_id'] = $id;
        $validator = Validator::make(
            $requestData,
            [
                'samu_unit_id' => ['required', 'exists:samu_units,id', 'gt:0']
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }

        if ($request->user()->cannot('delete', [SamuUnit::class, $id])) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $samuUnitService = new SamuService();
        $deleteResult = $samuUnitService->deleteSamuUnit($id);
        if ($deleteResult) {
            return new Response(['message' => 'Samu Unit deleted successfully!'], 200);
        }
        return new Response(['errors' => 'Error! Failed to delete the Samu Unit.'], 500);
    }
}
