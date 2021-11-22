<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Bed;
use App\Models\BedType;
use App\Models\City;
use App\Models\HealthUnit;
use App\Models\HealthUnitContact;
use App\Models\State;
use App\Models\User;
use App\Models\UserUnit;

class HealthUnitService
{
    /**
     * Persists a Health Unit into the database.
     * @param string $name
     * @param string $address
     * @param int $addressNumber
     * @param string $district
     * @param int $cityId
     * @param array $telephoneNumbers
     * @return bool True on success, false on failure.
     */
    public function createHealthUnit(
        string $name,
        string $address,
        int $addressNumber,
        string $district,
        int $cityId,
        array $telephoneNumbers
    ): bool {
        $addressService = new AddressService();
        $result = $addressService->createAddress(
            $address,
            $addressNumber,
            $district,
            $cityId
        );
        if (!$result) {
            return false;
        }
        $address = $addressService->getAddress($address);
        /** @var User $user */
        $user = auth()->user();
        $healthUnit = new HealthUnit([
            'name' => $name,
            'address_id' => $address->id,
            'created_by' => $user->id
        ]);
        $result = $healthUnit->save();
        if (!$result) {
            $address->delete();
            return false;
        }
        foreach ($telephoneNumbers as $telephoneNumber) {
            $healthUnitContact = new HealthUnitContact([
                'health_unit_id' => $healthUnit->id,
                'telephone_number' => $telephoneNumber,
                'created_by' => $user->id
            ]);
            if (!$healthUnitContact->save()) {
                return false;
            }
        }
        $userUnit = new UserUnit([
            'user_id' => $user->id,
            'samu_unit_id' => null,
            'health_unit_id' => $healthUnit->id,
            'created_by' => $user->id
        ]);
        $saveResult = $userUnit->save();
        if (!$saveResult) {
            return false;
        }
        return true;
    }

    /**
     * Updates a registered Health Unit.
     * @param int $healthUnitId
     * @param string|null $updatedName
     * @param array|null $updateAddressData
     * @param array|null $updatedTelephoneNumbers
     * @return bool True on success, false on failure.
     */
    public function updateHealthUnit(
        int $healthUnitId,
        string $updatedName = null,
        array $updateAddressData = null,
        array $updatedTelephoneNumbers = null
    ): bool {
        $healthUnit = (new HealthUnit())->find($healthUnitId);
        if ($updatedName) {
            $healthUnit->name = $updatedName;
        }
        if ($updateAddressData) {
            $addressService = new AddressService();
            $addressUpdateResult = $addressService->updateAddress(
                $healthUnit->address_id,
                $updateAddressData
            );
            if ($addressUpdateResult === false) {
                return false;
            }
        }
        if ($updatedTelephoneNumbers) {
            foreach ($updatedTelephoneNumbers as $updatedTelephoneNumber) {
                $contact = (new HealthUnitContact())->find($updatedTelephoneNumber['id']);
                $contact->telephone_number = $updatedTelephoneNumber['telephone_number'];
                $saveResult = $contact->save();
                if ($saveResult === false) {
                    return false;
                }
            }
        }
        return $healthUnit->save();
    }

    /**
     * Returns an array with data from a specified Health Unit.
     * @param int $healthUnitId
     * @return array The Health Unit data.
     */
    public function getHealthUnit(int $healthUnitId): array
    {
        $healthUnit = (new HealthUnit())
            ->find($healthUnitId)
            ->toArray();

        $address = (new Address())
            ->find($healthUnit['address_id'])
            ->toArray();

        $city = (new City())
            ->find($address['city_id'])
            ->toArray();

        $state = (new State())
            ->find($city['state_id'])
            ->toArray();

        $healthUnitContacts = (new HealthUnitContact())
            ->where('health_unit_id', '=', $healthUnitId)
            ->get()
            ->toArray();

        return [
            'health_unit' => $healthUnit,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'telephone_numbers' => $healthUnitContacts
        ];
    }

    /**
     * Returns all Health Units registered in the database.
     * @return array
     */
    public function getAllHealthUnits(): array
    {
        $resultArray = [];
        $healthUnits = HealthUnit::all();
        if (empty($healthUnits->toArray())) {
            return [];
        }
        foreach ($healthUnits as $healthUnitArrayKey => $healthUnit) {
            $healthUnit = $healthUnit
                ->toArray();

            $address = (new Address())
                ->find($healthUnit['address_id'])
                ->toArray();

            $city = (new City())
                ->find($address['city_id'])
                ->toArray();

            $state = (new State())
                ->find($city['state_id'])
                ->toArray();

            $healthUnitContacts = (new HealthUnitContact())
                ->where('health_unit_id', '=', $healthUnit['id'])
                ->get()
                ->toArray();

            $resultArray[$healthUnitArrayKey]['health_unit'] = $healthUnit;
            $resultArray[$healthUnitArrayKey]['address'] = $address;
            $resultArray[$healthUnitArrayKey]['city'] = $city;
            $resultArray[$healthUnitArrayKey]['state'] = $state;
            $resultArray[$healthUnitArrayKey]['telephone_numbers'] = $healthUnitContacts;
        }
        return $resultArray;
    }

    /**
     * Deletes a specified Health Unit from database.
     * @param int $healthUnitId
     * @return bool True on success, false on failure.
     */
    public function deleteHealthUnit(int $healthUnitId): bool
    {
        $userService = new UserService();
        $usersAttachedToThisHealthUnit = (new UserUnit())
            ->where('health_unit_id', '=', $healthUnitId)
            ->get();
        foreach ($usersAttachedToThisHealthUnit as $user) {
            $deleteResult = $userService->deleteUser($user->id);
            if ($deleteResult === false) {
                return false;
            }
        }

        $bedService = new BedService();
        $bedsAttachedToThisHealthUnit = (new Bed())
            ->where('health_unit_id', '=', $healthUnitId)
            ->get();
        foreach ($bedsAttachedToThisHealthUnit as $bed) {
            $deleteResult = $bedService->deleteBed($bed->id);
            if ($deleteResult === false) {
                return false;
            }
        }

        $healthUnit = (new HealthUnit())->find($healthUnitId);
        $healthUnitContacts = (new HealthUnitContact())
            ->where('health_unit_id', '=', $healthUnitId)
            ->get();
        foreach ($healthUnitContacts as $contact) {
            $deleteResult = $contact->delete();
            if ($deleteResult === false) {
                return false;
            }
        }
        $healthUnitAddress = (new Address())
            ->find($healthUnit->address_id);

        $deleteResult = $healthUnitAddress->delete();
        if ($deleteResult === false) {
            return false;
        }
        return $healthUnit->delete();
    }

    /**
     * Returns an array with data from all registered Health Units.
     * @return array
     */
    public function getAllHealthUnitsWithBeds(): array
    {
        $resultArray = [];
        $keysToUnset = [];
        $healthUnits = (new HealthUnit())->get();
        foreach ($healthUnits as $healthUnitArrayKey => $healthUnit) {
            $resultArray[$healthUnitArrayKey]['health_unit'] = $healthUnit->toArray();
            $beds = (new Bed())
                ->where('health_unit_id', '=', $healthUnit->id)
                ->get();
            if (empty($beds->toArray())) {
                $keysToUnset[] = $healthUnitArrayKey;
            }
            foreach ($beds as $key => $bed) {
                $bedType = (new BedType())
                    ->find($bed->bed_type_id);
                $bedArray[$key]['bed'] = $bed->toArray();
                $bedArray[$key]['bed_type'] = $bedType->toArray();
                $resultArray[$healthUnitArrayKey]['health_unit']['beds'] = $bedArray;
            }
        }
        foreach ($keysToUnset as $keyToUnset) {
            array_splice($resultArray, $keyToUnset, 1);
        }
        return $resultArray;
    }
}
