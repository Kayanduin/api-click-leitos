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
     * @param array $validatedData
     * @return bool
     */
    public function createSamuUnit(array $validatedData): bool
    {
        $addressService = new AddressService();
        $result = $addressService->createAddress($validatedData);
        if (!$result) {
            return false;
        }
        $address = $addressService->getAddress($validatedData);
        /** @var User $user */
        $user = auth()->user();
        $samuUnit = new SamuUnit([
            'name' => $validatedData['name'],
            'address_id' => $address->id,
            'created_by' => $user->id
        ]);
        $result = $samuUnit->save();
        if (!$result) {
            $address->delete();
            return false;
        }
        foreach ($validatedData['telephone_numbers'] as $telephoneNumber) {
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

    public function getSamuUnit(int $samuUnitId): array
    {
        $responseArray['health_unit'] = (new SamuUnit())->find($samuUnitId)->toArray();
        $responseArray['address'] = (new Address())
            ->find(
                $responseArray['health_unit']['address_id']
            )->toArray();
        $responseArray['city'] = (new City())->find($responseArray['address']['city_id'])->toArray();
        $responseArray['state'] = (new State())->find($responseArray['city']['state_id'])->toArray();
        $healthUnitContacts = (new SamuUnitContact())->where('samu_unit_id', $samuUnitId)->get();
        $responseArray['telephone_numbers'] = $healthUnitContacts->toArray();
        return $responseArray;
    }

    public function updateSamuUnit(array $validatedData): bool
    {
        $healthUnit = (new SamuUnit())->find($validatedData['samu_unit_id']);
        $addressService = new AddressService();
        $addressUpdateResult = $addressService->updateAddress($healthUnit->address_id, $validatedData);
        if (!$addressUpdateResult) {
            return false;
        }
        foreach ($validatedData as $key => $value) {
            switch ($key) {
                case 'name':
                    $healthUnit->name = $value;
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
        return $healthUnit->save();
    }

    public function deleteSamuUnit(int $samuUnitId): bool
    {
        $usersAttachedToThisSamuUnit = (new UserUnit())->where('samu_unit_id', '=', $samuUnitId)->get();
        $userService = new UserService();
        foreach ($usersAttachedToThisSamuUnit as $user) {
            $deleteResult = $userService->deleteUser($user->id);
            if ($deleteResult === false) {
                return false;
            }
        }

        $samuUnit = (new SamuUnit())->find($samuUnitId);
        $samuUnitContacts = (new SamuUnitContact())->where('samu_unit_id', $samuUnitId)->get();
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
