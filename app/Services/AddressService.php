<?php

namespace App\Services;

use App\Models\Address;
use App\Models\City;
use App\Models\State;
use App\Models\User;

class AddressService
{
    /**
     * Return all states registered in the database.
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
     * Return all cities of a given state ID, that are registered in the database.
     * @param int $stateId
     * @return array
     */
    public function getCitiesByStates(int $stateId): array
    {
        $cities = (new City())
            ->where('state_id', '=', $stateId)
            ->get();
        return [
            "meta" => [
                'number_of_records' => count($cities)
            ],
            "cities" => $cities
        ];
    }

    /**
     * Persist an address in the database.
     * @param string $address
     * @param int $addressNumber
     * @param string $district
     * @param int $cityId
     * @return bool False on error, true on success.
     */
    public function createAddress(string $address, int $addressNumber, string $district, int $cityId): bool
    {
        /** @var User $user */
        $user = auth()->user();
        $address = new Address([
            'address' => $address,
            'address_number' => $addressNumber,
            'district' => $district,
            'city_id' => $cityId,
            'created_by' => $user->id
        ]);
        return $address->save();
    }

    /**
     * Returns the address.
     * @param string $address
     * @return Address|null An address model if address is found, false otherwise.
     */
    public function getAddress(string $address): Address|null
    {
        return (new Address())
            ->where('address', '=', $address)
            ->first();
    }

    /**
     * Delete the specified address from the database.
     * @param int $addressId
     */
    public function deleteAddress(int $addressId): void
    {
        $address = (new Address())->find($addressId);
        $address->delete();
    }

    /**
     * Updates the registered address.
     * @param int $addressId
     * @param array $newAddressDataArray
     * @return bool True on successful save, false otherwise.
     */
    public function updateAddress(int $addressId, array $newAddressDataArray): bool
    {
        $address = (new Address())->find($addressId);
        foreach ($newAddressDataArray as $key => $value) {
            switch ($key) {
                case 'address':
                    $address->address = $value;
                    break;
                case 'address_number':
                    $address->address_number = $value;
                    break;
                case 'district':
                    $address->district = $value;
                    break;
                case 'city_id':
                    $address->city_id = $value;
                    break;
                default:
                    break;
            }
        }
        return $address->save();
    }
}
