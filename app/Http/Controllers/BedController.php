<?php

namespace App\Http\Controllers;

use App\Services\BedService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BedController extends Controller
{
    /**
     * @param int $bedId
     * @return Response
     */
    public function getBedById(int $bedId): Response
    {
        $validator = Validator::make(
            ['bed_id' => $bedId],
            ['bed_id' => ['required', 'integer', 'exists:beds,id', 'gt:0']]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }
        $bedService = new BedService();
        $bed = $bedService->getBedById($bedId);

        return new Response($bed, 200);
    }

    /**
     * @param int $healthUnitId
     * @return Response
     */
    public function getBedsByHealthUnit(int $healthUnitId): Response
    {
        $validator = Validator::make(
            ['health_unit_id' => $healthUnitId],
            [
                'health_unit_id' => [
                    'required',
                    'integer',
                    'exists:health_units,id',
                    'exists:beds,health_unit_id',
                    'gt:0'
                ]
            ],
            [
                'health_unit_id.exists' => 'This health unit does not have registered beds.'
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }
        $bedService = new BedService();
        $beds = $bedService->getBedsByHealthUnit($healthUnitId);

        return new Response($beds, 200);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function createBed(Request $request): Response
    {
        $validator = Validator::make(
            $request->toArray(),
            [
                'bed_type_id' => ['required', 'exists:bed_types,id', 'integer', 'gt:0'],
                'total_beds' => ['required', 'integer', 'gt:0'],
                'health_unit_id' => ['required', 'integer', 'exists:health_units,id', 'gt:0']
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
        $bedService = new BedService();
        $isBedCreated = $bedService->createBed($validatedData);

        if ($isBedCreated) {
            return new Response(['message' => 'Bed created successfully!'], 201);
        }
        return new Response(['errors' => 'Error! The bed could not be created.'], 400);
    }

    /**
     * @param Request $request
     * @param int $bedId
     * @return Response
     */
    public function updateBed(Request $request, int $bedId): Response
    {
        $request = $request->toArray();
        $request['bed_id'] = $bedId;
        $validator = Validator::make(
            $request,
            [
                'total_beds' => ['required', 'integer', 'gt:0'],
                'bed_id' => ['required', 'integer', 'exists:beds,id', 'gt:0']
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
        $bedService = new BedService();
        $isBedUpdated = $bedService->updateBed($validatedData);

        if ($isBedUpdated) {
            return new Response(['message' => 'Bed updated successfully!'], 200);
        }
        return new Response(['errors' => 'Error! The bed could not be updated.'], 400);
    }

    /**
     * @param int $bedId
     * @return Response
     */
    public function deleteBed(int $bedId): Response
    {
        $validator = Validator::make(
            ['bed_id' => $bedId],
            ['bed_id' => ['required', 'integer', 'exists:beds,id', 'gt:0']]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }
        $bedService = new BedService();
        $isBedDeleted = $bedService->deleteBed($bedId);
        if ($isBedDeleted) {
            return new Response(['message' => 'Bed deleted successfully!'], 200);
        }
        return new Response(['errors' => 'Error! The bed could not be deleted.'], 400);
    }

    /**
     * @param Request $request
     * @param int $bedId
     * @return Response
     */
    public function increaseFreeBeds(Request $request, int $bedId): Response
    {
        $request = $request->toArray();
        $request['bed_id'] = $bedId;
        $validator = Validator::make(
            $request,
            [
                'freed_beds_number' => ['required', 'integer', 'gt:0'],
                'bed_id' => ['required', 'integer', 'exists:beds,id', 'gt:0']
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
        $bedService = new BedService();
        $isBedUpdated = $bedService->increaseFreeBeds($validatedData);
        if ($isBedUpdated) {
            return new Response(['message' => 'Free beds increased successfully!'], 200);
        }
        return new Response(['errors' => 'Error! The number of free beds could not be increased.'], 400);
    }

    /**
     * @param Request $request
     * @param int $bedId
     * @return Response
     */
    public function decreaseFreeBeds(Request $request, int $bedId): Response
    {
        $request = $request->toArray();
        $request['bed_id'] = $bedId;
        $validator = Validator::make(
            $request,
            [
                'occupied_beds_number' => ['required', 'integer', 'gt:0'],
                'bed_id' => ['required', 'integer', 'exists:beds,id', 'gt:0']
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
        $bedService = new BedService();
        $isBedUpdated = $bedService->decreaseFreeBeds($validatedData);
        if ($isBedUpdated) {
            return new Response(['message' => 'Free beds decreased successfully!'], 200);
        }
        return new Response(['errors' => 'Error! The number of free beds could not be decreased.'], 400);
    }

    /**
     * @return Response
     */
    public function getBedTypes(): Response
    {
        $bedService = new BedService();
        $bedTypes = $bedService->getBedTypes();
        return new Response($bedTypes, 200);
    }
}
