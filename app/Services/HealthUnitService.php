<?php

namespace App\Services;

use App\Models\Address;
use App\Models\City;
use App\Models\HealthUnit;
use App\Models\HealthUnitContact;
use App\Models\State;

class HealthUnitService
{
    public function createHealthUnit(array $data): bool
    {
        $addressService = new AddressService();
        $result = $addressService->createAddress($data);
        if (!$result) {
            return false;
        }
        $address = $addressService->getAddress($data);
        $healthUnit = new HealthUnit([
            'name' => $data['name'],
            'address_id' => $address->id,
            'created_by' => 1
        ]);
        $result = $healthUnit->save();
        if (!$result) {
            $address->delete();
            return false;
        }
        foreach ($data['telephone_numbers'] as $telephoneNumber) {
            $healthUnitContact = new HealthUnitContact([
                'health_unit_id' => $healthUnit->id,
                'telephone_number' => $telephoneNumber,
                'created_by' => 1
            ]);
            if (!$healthUnitContact->save()) {
                return false;
            }
        }
        return true;
    }

    public function updateHealthUnit(array $data): bool
    {
        $healthUnit = (new HealthUnit())->find($data['healthUnitId']);
        $addressService = new AddressService();
        $addressUpdateResult = $addressService->updateAddress($healthUnit->address_id, $data);
        if (!$addressUpdateResult) {
            return false;
        }
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'name':
                    $healthUnit->name = $value;
                    break;
                case 'telephone_numbers':
                    foreach ($value as $updatedContact) {
                        $contact = (new HealthUnitContact())->find($updatedContact['id']);
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

    public function getHealthUnit(int $healthUnitId): array
    {
        $responseArray['health_unit'] = (new HealthUnit())->find($healthUnitId)->toArray();
        $responseArray['address'] = (new Address())
            ->find(
                $responseArray['health_unit']['address_id']
            )->toArray();
        $responseArray['city'] = (new City())->find($responseArray['address']['city_id'])->toArray();
        $responseArray['state'] = (new State())->find($responseArray['city']['state_id'])->toArray();
        $healthUnitContacts = (new HealthUnitContact())->where('health_unit_id', $healthUnitId)->get();
        $responseArray['telephone_numbers'] = $healthUnitContacts->toArray();
        return $responseArray;
    }

    public function getAllHealthUnits(): ?array
    {
        $resultArray = [];
        $healthUnits = HealthUnit::all();
        if (empty($healthUnits->toArray())) {
            return null;
        }
        foreach ($healthUnits as $healthUnitArrayKey => $healthUnit) {
            $resultArray[$healthUnitArrayKey]['health_unit'] = $healthUnit->toArray();
            $resultArray[$healthUnitArrayKey]['address'] = (new Address())->find(
                $healthUnit->getAttribute('address_id')
            )->toArray();
            $resultArray[$healthUnitArrayKey]['city'] = (new City())->find(
                $resultArray[$healthUnitArrayKey]['address']['city_id']
            )->toArray();
            $resultArray[$healthUnitArrayKey]['state'] = (new State())->find(
                $resultArray[$healthUnitArrayKey]['city']['state_id']
            )->toArray();
            $healthUnitId = $healthUnit->getAttribute('id');
            $healthUnitContacts = (new HealthUnitContact())->where('health_unit_id', $healthUnitId)->get();
            $resultArray[$healthUnitArrayKey]['telephone_numbers'] = $healthUnitContacts->toArray();
        }
        return $resultArray;
    }

    public function deleteHealthUnit(int $healthUnitId): ?bool
    {
        $healthUnit = (new HealthUnit())->find($healthUnitId);
        $healthUnitContacts = (new HealthUnitContact())->where('health_unit_id', $healthUnitId)->get();
        foreach ($healthUnitContacts as $contact) {
            $deleteResult = $contact->delete();
            if ($deleteResult === false) {
                return false;
            }
        }
        $deleteResult = $healthUnit->delete();
        if ($deleteResult === false) {
            return false;
        }
        $healthUnitAddress = (new Address())->find($healthUnit->getAttribute('address_id'));

        return $healthUnitAddress->delete();
    }
}
