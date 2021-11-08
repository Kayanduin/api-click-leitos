<?php

namespace App\Services;

use App\Models\Address;
use App\Models\City;
use App\Models\State;
use App\Models\User;

class AddressService
{
    /**
     * @return array
     */
    public function getStates(): array
    {
        $states = State::all();
        $states = $states->toArray();
        return [
            "meta" => [
                'number_of_records' => count($states)
            ],
            "states" => $states
        ];
    }

    /**
     * @param int $stateId
     * @return array
     */
    public function getCitiesByStates(int $stateId): array
    {
        $cities = (new City())->where('state_id', $stateId)->get();
        return [
            "meta" => [
                'number_of_records' => count($cities)
            ],
            "cities" => $cities
        ];
    }

    public function createAddress(array $data): bool
    {
        /** @var User $user */
        $user = auth()->user();
        $address = new Address([
            'address' => $data['address'],
            'address_number' => $data['address_number'],
            'district' => $data['district'],
            'city_id' => $data['city_id'],
            'created_by' => $user->id
        ]);
        return $address->save();
    }

    public function getAddress(array $data): Address|null
    {
        return (new Address())
            ->where('address', $data['address'])
            ->first();
    }

    public function deleteAddress(int $addressId): void
    {
        $address = (new Address())->find($addressId);
        $address->delete();
    }

    public function updateAddress(int $addressId, array $data): bool
    {
        $healthUnityAddress = (new Address())->find($addressId);
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'address':
                    $healthUnityAddress->address = $value;
                    break;
                case 'address_number':
                    $healthUnityAddress->address_number = $value;
                    break;
                case 'district':
                    $healthUnityAddress->district = $value;
                    break;
                case 'city_id':
                    $healthUnityAddress->city_id = $value;
                    break;
                default:
                    break;
            }
        }
        return $healthUnityAddress->save();
    }
}
