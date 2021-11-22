<?php

namespace App\Services;

use App\Models\Bed;
use App\Models\BedType;
use App\Models\HealthUnit;
use App\Models\User;
use App\Models\UserUnit;

class BedService
{
    /**
     * Persists a health unit bed in the database.
     * @param int $bedTypeId
     * @param int $totalBedsNumber
     * @param int $healthUnitId
     * @return bool True on success, false on failure.
     */
    public function createBed(int $bedTypeId, int $totalBedsNumber, int $healthUnitId): bool
    {
        /** @var User $user */
        $user = auth()->user();

        $bed = new Bed([
            'bed_type_id' => $bedTypeId,
            'total_beds' => $totalBedsNumber,
            'free_beds' => $totalBedsNumber,
            'health_unit_id' => $healthUnitId,
            'created_by' => $user->id
        ]);
        return $bed->save();
    }

    /**
     * Retrieves from the database the specified bed by its ID.
     * @param int $bedId
     * @return array An array with the bed data.
     */
    public function getBedById(int $bedId): array
    {
        $bed = (new Bed())->find($bedId);
        $bedType = (new BedType())->find($bed->bed_type_id);
        $healthUnit = (new HealthUnit())->find($bed->health_unit_id);
        return [
            'bed' => $bed->toArray(),
            'bed_type' => $bedType->toArray(),
            'health_unit' => $healthUnit->toArray()
        ];
    }

    /**
     * Returns all beds that belongs to a specified Health Unit
     * @param int $healthUnitId
     * @return array An array with the bed and health unit data.
     */
    public function getBedsByHealthUnit(int $healthUnitId): array
    {
        $resultArray = [];
        $beds = (new Bed())
            ->where('health_unit_id', '=', $healthUnitId)
            ->get();
        foreach ($beds as $key => $bed) {
            $resultArray[$key]['bed'] = $bed->toArray();
            $resultArray[$key]['bed_type'] = (new BedType())->find($bed->bed_type_id);
            $resultArray[$key]['health_unit'] = (new HealthUnit())->find($healthUnitId);
        }
        return $resultArray;
    }

    /**
     * Updates a registered bed.
     * @param int $bedId
     * @param int $newTotalBedsNumber
     * @return bool True on success, false on failure.
     */
    public function updateBed(int $bedId, int $newTotalBedsNumber): bool
    {
        $bed = (new Bed())->find($bedId);
        $bed->total_beds = $newTotalBedsNumber;
        if ($newTotalBedsNumber < $bed->free_beds) {
            $bed->free_beds = $newTotalBedsNumber;
        }
        return $bed->save();
    }

    /**
     * Deletes a registered bed from database.
     * @param int $bedId
     * @return bool True on success, false on failure.
     */
    public function deleteBed(int $bedId): bool
    {
        $bed = (new Bed())->find($bedId);
        return $bed->delete();
    }

    /**
     * Increase the number of free beds by bed ID.
     * @param int $bedId
     * @param int $freedBedsNumber
     * @return bool True on success, false on failure.
     */
    public function increaseFreeBeds(int $bedId, int $freedBedsNumber): bool
    {
        $bed = (new Bed())->find($bedId);
        $updatedFreeBedsAmount = $bed->free_beds + $freedBedsNumber;
        if ($updatedFreeBedsAmount > $bed->total_beds) {
            return false;
        }
        $bed->free_beds = $updatedFreeBedsAmount;
        $saveResult = $bed->save();
        if ($saveResult === false) {
            return false;
        }
        $this->notifyUpdatedBedToSamuUnitUsers('liberado', $bed);
        return true;
    }

    /**
     * Decrease the number of free beds by bed ID.
     * @param int $bedId
     * @param int $occupiedBedsNumber
     * @return bool True on success, false on failure.
     */
    public function decreaseFreeBeds(int $bedId, int $occupiedBedsNumber): bool
    {
        $bed = (new Bed())->find($bedId);
        $updatedFreeBedsAmount = $bed->free_beds - $occupiedBedsNumber;
        if ($updatedFreeBedsAmount < 0) {
            return false;
        }
        $bed->free_beds = $updatedFreeBedsAmount;
        $saveResult = $bed->save();
        if ($saveResult === false) {
            return false;
        }
        $this->notifyUpdatedBedToSamuUnitUsers('ocupado', $bed);
        return true;
    }

    /**
     * Returns an array with all registered bed types.
     * @return array An array with all registered bed types.
     */
    public function getBedTypes(): array
    {
        return (new BedType())
            ->get()
            ->toArray();
    }

    /**
     * Sends an email to all users of the Health Unit that holds the specified bed. The email informs that an ambulance
     * is on its way to the Health Unit and will need one bed of this type.
     * @param int $bedId
     */
    public function notifyBedManagers(int $bedId): void
    {
        $mailService = new MailService();
        $bed = (new Bed())->find($bedId);
        $userUnitAttachedToBedHealthUnit = (new UserUnit())
            ->where('health_unit_id', '=', $bed->health_unit_id)
            ->get();
        foreach ($userUnitAttachedToBedHealthUnit as $userUnit) {
            $user = (new User())->find($userUnit->user_id);
            $mailService->sendBedManagersNotificationMail(
                $user->email,
                $bed->getBedType()
            );
        }
    }

    /**
     * Sends an email to all users of all Samu Units. The email informs that a bed were updated (freed or occupied).
     * @param string $typeOfActionDone
     * @param Bed $bed
     */
    private function notifyUpdatedBedToSamuUnitUsers(string $typeOfActionDone, Bed $bed): void
    {
        $mailService = new MailService();
        $userUnitAttachedToSamuUnit = (new UserUnit())
            ->whereNotNull('samu_unit_id')
            ->get();
        foreach ($userUnitAttachedToSamuUnit as $userUnit) {
            $user = (new User())->find($userUnit->user_id);
            $mailService->sendFreeBedNumberUpdateMail(
                $user->email,
                $typeOfActionDone,
                $bed->getBedHealthUnit()->name,
                $bed->getBedType(),
                $bed->total_beds,
                $bed->free_beds
            );
        }
    }
}
