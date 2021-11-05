<?php

namespace App\Services;

use App\Models\Bed;
use App\Models\BedType;
use App\Models\HealthUnit;
use App\Models\User;

class BedService
{
    /**
     * @param array $data
     * @return bool
     */
    public function createBed(array $data): bool
    {
        /** @var User $user */
        $user = auth()->user();

        $bed = new Bed([
            'bed_type_id' => $data['bed_type_id'],
            'total_beds' => $data['total_beds'],
            'free_beds' => $data['total_beds'],
            'health_unit_id' => $data['health_unit_id'],
            'created_by' => $user->id
        ]);
        return $bed->save();
    }

    /**
     * @param int $bedId
     * @return array
     */
    public function getBedById(int $bedId): array
    {
        $bed = (new Bed())->find($bedId);
        $bedType = (new BedType())->find($bed->getAttribute('bed_type_id'));
        $healthUnit = (new HealthUnit())->find($bed->getAttribute('health_unit_id'));
        return [
            'bed' => $bed->toArray(),
            'bed_type' => $bedType->toArray(),
            'health_unit' => $healthUnit->toArray()
        ];
    }

    /**
     * @param int $healthUnitId
     * @return array
     */
    public function getBedsByHealthUnit(int $healthUnitId): array
    {
        $resultArray = [];
        $beds = (new Bed())->where('health_unit_id', $healthUnitId)->get();
        foreach ($beds as $key => $bed) {
            $resultArray[$key]['bed'] = $bed->toArray();
            $resultArray[$key]['bed_type'] = (new BedType())->find($bed->getAttribute('bed_type_id'));
            $resultArray[$key]['health_unit'] = (new HealthUnit())->find($healthUnitId);
        }
        return $resultArray;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function updateBed(array $data): bool
    {
        $bed = (new Bed())->find($data['bed_id']);
        $bed->total_beds = $data['total_beds'];
        if ($data['total_beds'] < $bed->free_beds) {
            $bed->free_beds = $data['total_beds'];
        }
        return $bed->save();
    }

    /**
     * @param int $bedId
     * @return bool|null
     */
    public function deleteBed(int $bedId): ?bool
    {
        $bed = (new Bed())->find($bedId);
        return $bed->delete();
    }

    /**
     * @param array $data
     * @return bool
     */
    public function increaseFreeBeds(array $data): bool
    {
        $bed = (new Bed())->find($data['bed_id']);
        $updatedFreeBedsAmount = $bed->free_beds + $data['freed_beds_number'];
        if ($updatedFreeBedsAmount > $bed->total_beds) {
            return false;
        }
        $bed->free_beds = $updatedFreeBedsAmount;
        return $bed->save();
    }

    /**
     * @param array $data
     * @return bool
     */
    public function decreaseFreeBeds(array $data): bool
    {
        $bed = (new Bed())->find($data['bed_id']);
        $updatedFreeBedsAmount = $bed->free_beds - $data['occupied_beds_number'];
        if ($updatedFreeBedsAmount < 0) {
            return false;
        }
        $bed->free_beds = $updatedFreeBedsAmount;
        return $bed->save();
    }

    /**
     * @return array
     */
    public function getBedTypes(): array
    {
        return (new BedType())->get()->toArray();
    }
}
