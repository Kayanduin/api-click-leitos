<?php

namespace App\Services;

use App\Models\Address;
use App\Models\City;
use App\Models\SamuUnit;
use App\Models\SamuUnitContact;
use App\Models\State;
use App\Models\User;
use App\Models\UserUnit;

class SamuService
{

    /**
     * Persists a SAMU Unit into the database.
     * @param string $name
     * @param string $address
     * @param int $addressNumber
     * @param string $district
     * @param int $cityId
     * @param array $telephoneNumbers
     * @return bool True on success, false on failure.
     */
    public function createSamuUnit(
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
        $samuUnit = new SamuUnit([
            'name' => $name,
            'address_id' => $address->id,
            'created_by' => $user->id
        ]);
        $result = $samuUnit->save();
        if (!$result) {
            $address->delete();
            return false;
        }
        foreach ($telephoneNumbers as $telephoneNumber) {
            $healthUnitContact = new SamuUnitContact([
                'samu_unit_id' => $samuUnit->id,
                'telephone_number' => $telephoneNumber,
                'created_by' => $user->id
            ]);
            if (!$healthUnitContact->save()) {
                return false;
            }
        }
        $userUnit = new UserUnit([
            'user_id' => $user->id,
            'samu_unit_id' => $samuUnit->id,
            'health_unit_id' => null,
            'created_by' => $user->id
        ]);
        $saveResult = $userUnit->save();
        if (!$saveResult) {
            return false;
        }
        return true;
    }

    /**
     * Returns an array with data from a specified SAMU Unit.
     * @param int $samuUnitId
     * @return array An array with data of the SAMU Unit.
     */
    public function getSamuUnit(int $samuUnitId): array
    {
        $samuUnit = (new SamuUnit())
            ->find($samuUnitId)
            ->toArray();
        $address = (new Address())
            ->find($samuUnit['address_id'])
            ->toArray();
        $city = (new City())
            ->find($address['city_id'])
            ->toArray();
        $state = (new State())
            ->find($city['state_id'])
            ->toArray();
        $samuUnitContacts = (new SamuUnitContact())
            ->where('samu_unit_id', '=', $samuUnitId)
            ->get()
            ->toArray();
        return [
            'health_unit' => $samuUnit,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'telephone_numbers' => $samuUnitContacts
        ];
    }

    /**
     * Updates a registered SAMU Unit.
     * @param int $samuUnitId
     * @param array $updatedData
     * @return bool True on success, false on failure.
     */
    public function updateSamuUnit(int $samuUnitId, array $updatedData): bool
    {
        $addressService = new AddressService();
        $samuUnit = (new SamuUnit())->find($samuUnitId);
        $addressUpdateResult = $addressService->updateAddress($samuUnit->address_id, $updatedData);
        if ($addressUpdateResult === false) {
            return false;
        }
        foreach ($updatedData as $key => $value) {
            switch ($key) {
                case 'name':
                    $samuUnit->name = $value;
                    break;
                case 'telephone_numbers':
                    foreach ($value as $updatedContact) {
                        $contact = (new SamuUnitContact())->find($updatedContact['id']);
                        $contact->telephone_number = $updatedContact['telephone_number'];
                        $saveResult = $contact->save();
                        if ($saveResult === false) {
                            return false;
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        return $samuUnit->save();
    }

    /**
     * Deletes a specified SAMU Unit from database.
     * @param int $samuUnitId
     * @return bool True on success, false on failure.
     */
    public function deleteSamuUnit(int $samuUnitId): bool
    {
        $userService = new UserService();
        $usersAttachedToThisSamuUnit = (new UserUnit())
            ->where('samu_unit_id', '=', $samuUnitId)
            ->get();
        foreach ($usersAttachedToThisSamuUnit as $user) {
            $deleteResult = $userService->deleteUser($user->id);
            if ($deleteResult === false) {
                return false;
            }
        }

        $samuUnit = (new SamuUnit())->find($samuUnitId);
        $samuUnitContacts = (new SamuUnitContact())
            ->where('samu_unit_id', '=', $samuUnitId)
            ->get();
        foreach ($samuUnitContacts as $contact) {
            $deleteResult = $contact->delete();
            if ($deleteResult === false) {
                return false;
            }
        }
        $samuUnitAddress = (new Address())->find($samuUnit->address_id);

        $deleteResult = $samuUnitAddress->delete();
        if ($deleteResult === false) {
            return false;
        }

        return $samuUnit->delete();
    }
}
