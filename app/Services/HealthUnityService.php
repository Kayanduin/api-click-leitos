<?php

namespace App\Services;

use App\Models\Address;
use App\Models\City;
use App\Models\HealthUnity;
use App\Models\HealthUnityContact;
use App\Models\State;

class HealthUnityService
{
    public function createHealthUnity(array $data): bool
    {
        $addressService = new AddressService();
        $result = $addressService->createAddress($data);
        if (!$result) {
            return false;
        }
        $address = $addressService->getAddress($data);
        $healthUnity = new HealthUnity([
            'name' => $data['name'],
            'address_id' => $address->id,
            'created_by' => 1
        ]);
        $result = $healthUnity->save();
        if (!$result) {
            $address->delete();
            return false;
        }
        foreach ($data['telephone_numbers'] as $telephoneNumber) {
            $healthUnityContact = new HealthUnityContact([
                'health_unity_id' => $healthUnity->id,
                'telephone_number' => $telephoneNumber,
                'created_by' => 1
            ]);
            if (!$healthUnityContact->save()) {
                return false;
            }
        }
        return true;
    }

    public function updateHealthUnity(array $data): bool
    {
        $healthUnity = (new HealthUnity())->find($data['healthUnityId']);
        $addressService = new AddressService();
        $addressUpdateResult = $addressService->updateAddress($healthUnity->address_id, $data);
        if (!$addressUpdateResult) {
            return false;
        }
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'name':
                    $healthUnity->name = $value;
                    break;
                case 'telephone_numbers':
                    foreach ($value as $updatedContact) {
                        $contact = (new HealthUnityContact())->find($updatedContact['id']);
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
        return $healthUnity->save();
    }

    public function getHealthUnity(int $healthUnityId): array
    {
        $responseArray['health_unity'] = (new HealthUnity())->find($healthUnityId)->toArray();
        $responseArray['address'] = (new Address())
            ->find(
                $responseArray['health_unity']['address_id']
            )->toArray();
        $responseArray['city'] = (new City())->find($responseArray['address']['city_id'])->toArray();
        $responseArray['state'] = (new State())->find($responseArray['city']['state_id'])->toArray();
        $healthUnityContacts = (new HealthUnityContact())->where('health_unity_id', $healthUnityId)->get();
        $responseArray['telephone_numbers'] = $healthUnityContacts->toArray();
        return $responseArray;
    }

    public function getAllHealthUnities(): ?array
    {
        $resultArray = [];
        $healthUnities = HealthUnity::all();
        if (empty($healthUnities->toArray())) {
            return null;
        }
        foreach ($healthUnities as $healthUnityArrayKey => $healthUnity) {
            $resultArray[$healthUnityArrayKey]['health_unity'] = $healthUnity->toArray();
            $resultArray[$healthUnityArrayKey]['address'] = (new Address())->find(
                $healthUnity->getAttribute('address_id')
            )->toArray();
            $resultArray[$healthUnityArrayKey]['city'] = (new City())->find(
                $resultArray[$healthUnityArrayKey]['address']['city_id']
            )->toArray();
            $resultArray[$healthUnityArrayKey]['state'] = (new State())->find(
                $resultArray[$healthUnityArrayKey]['city']['state_id']
            )->toArray();
            $healthUnityId = $healthUnity->getAttribute('id');
            $healthUnityContacts = (new HealthUnityContact())->where('health_unity_id', $healthUnityId)->get();
            $resultArray[$healthUnityArrayKey]['telephone_numbers'] = $healthUnityContacts->toArray();
        }
        return $resultArray;
    }

    public function deleteHealthUnity(int $healthUnityId): ?bool
    {
        $healthUnity = (new HealthUnity())->find($healthUnityId);
        $healthUnityContacts = (new HealthUnityContact())->where('health_unity_id', $healthUnityId)->get();
        foreach ($healthUnityContacts as $contact) {
            $deleteResult = $contact->delete();
            if ($deleteResult === false) {
                return false;
            }
        }
        $deleteResult = $healthUnity->delete();
        if ($deleteResult === false) {
            return false;
        }
        $healthUnityAddress = (new Address())->find($healthUnity->getAttribute('address_id'));

        return $healthUnityAddress->delete();
    }
}
