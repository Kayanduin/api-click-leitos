<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Services\BedService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BedController extends Controller
{
    /**
     * @param Request $request
     * @param int $bedId
     * @return Response
     */
    public function getBedById(Request $request, int $bedId): Response
    {
        $validator = Validator::make(
            ['bed_id' => $bedId],
            ['bed_id' => ['required', 'integer', 'exists:beds,id', 'gt:0']]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }
        if ($request->user()->cannot('viewById', [Bed::class, $bedId])) {
            return new Response(['errors' => 'Access denied.'], 403);
        }
        $bedService = new BedService();
        $bedData = $bedService->getBedById($bedId);

        return new Response($bedData, 200);
    }

    /**
     * @param Request $request
     * @param int $healthUnitId
     * @return Response
     */
    public function getBedsByHealthUnit(Request $request, int $healthUnitId): Response
    {
        if ($request->user()->cannot('viewByHealthUnitId', [Bed::class, $healthUnitId])) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $validator = Validator::make(
            ['health_unit_id' => $healthUnitId],
            [
                'health_unit_id' => [
                    'required',
                    'integer',
                    'exists:health_units,id',
                    'gt:0'
                ]
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }
        $bedService = new BedService();
        $healthUnitBedsData = $bedService->getBedsByHealthUnit($healthUnitId);

        return new Response($healthUnitBedsData, 200);
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

        $bed = (new Bed())
            ->where('bed_type_id', '=', $validatedData['bed_type_id'])
            ->where('health_unit_id', '=', $validatedData['health_unit_id'])
            ->get();

        if (empty($bed->toArray()) === false) {
            return new Response(['errors' => 'A bed of this type is already registered in this Health Unit.'], 403);
        }

        if (
            $request->user()->cannot(
                'create',
                [Bed::class, $validatedData['health_unit_id']]
            )
        ) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $bedService = new BedService();
        $isBedCreated = $bedService->createBed(
            $validatedData['bed_type_id'],
            $validatedData['total_beds'],
            $validatedData['health_unit_id']
        );

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
        $requestArray = $request->toArray();
        $requestArray['bed_id'] = $bedId;
        $validator = Validator::make(
            $requestArray,
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

        $bed = (new Bed())->find($bedId);
        if (
            $request->user()->cannot(
                'update',
                [Bed::class, $bed]
            )
        ) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $bedService = new BedService();
        $isBedUpdated = $bedService->updateBed($validatedData['bed_id'], $validatedData['total_beds']);

        if ($isBedUpdated) {
            return new Response(['message' => 'Bed updated successfully!'], 200);
        }
        return new Response(['errors' => 'Error! The bed could not be updated.'], 400);
    }

    /**
     * @param Request $request
     * @param int $bedId
     * @return Response
     */
    public function deleteBed(Request $request, int $bedId): Response
    {
        $validator = Validator::make(
            ['bed_id' => $bedId],
            ['bed_id' => ['required', 'integer', 'exists:beds,id', 'gt:0']]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }

        $bed = (new Bed())->find($bedId);
        if (
            $request->user()->cannot(
                'delete',
                [Bed::class, $bed]
            )
        ) {
            return new Response(['errors' => 'Access denied.'], 403);
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
        $requestArray = $request->toArray();
        $requestArray['bed_id'] = $bedId;
        $validator = Validator::make(
            $requestArray,
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

        $bed = (new Bed())->find($bedId);
        if (
            $request->user()->cannot(
                'canAlterFreeBedNumber',
                [Bed::class, $bed]
            )
        ) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $bedService = new BedService();
        $isBedUpdated = $bedService->increaseFreeBeds($validatedData['bed_id'], $validatedData['freed_beds_number']);
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
        $requestArray = $request->toArray();
        $requestArray['bed_id'] = $bedId;
        $validator = Validator::make(
            $requestArray,
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

        $bed = (new Bed())->find($bedId);
        if (
            $request->user()->cannot(
                'canAlterFreeBedNumber',
                [Bed::class, $bed]
            )
        ) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $bedService = new BedService();
        $isBedUpdated = $bedService->decreaseFreeBeds($validatedData['bed_id'], $validatedData['occupied_beds_number']);
        if ($isBedUpdated) {
            return new Response(['message' => 'Free beds decreased successfully!'], 200);
        }
        return new Response(['errors' => 'Error! The number of free beds could not be decreased.'], 400);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getBedTypes(Request $request): Response
    {
        if ($request->user()->cannot('viewBedType', Bed::class)) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $bedService = new BedService();
        $bedTypes = $bedService->getBedTypes();
        return new Response($bedTypes, 200);
    }

    public function notifyBedManagers(Request $request, int $bedId): Response
    {
        $requestArray['bed_id'] = $bedId;
        $validator = Validator::make(
            $requestArray,
            [
                'bed_id' => ['required', 'integer', 'exists:beds,id', 'gt:0']
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return new Response(['errors' => $errors->all()], 400);
        }

        $bedService = new BedService();
        $bedService->notifyBedManagers($bedId);

        return new Response(['message' => 'Notification sent.'], 200);
    }
}
