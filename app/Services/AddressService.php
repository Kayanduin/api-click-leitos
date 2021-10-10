<?php

namespace App\Services;

use App\Models\City;
use App\Models\State;

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
        $cities = City::where('state_id', $stateId)->get();
        return [
            "meta" => [
                'number_of_records' => count($cities)
            ],
            "cities" => $cities
        ];
    }
}
