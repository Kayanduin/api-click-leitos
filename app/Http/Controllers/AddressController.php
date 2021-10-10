<?php

namespace App\Http\Controllers;

use App\Services\AddressService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AddressController extends Controller
{
    public function getStates(): Response
    {
        $addressService = new AddressService();
        $states = $addressService->getStates();

        return new Response($states, 200);
    }

    public function getCitiesByState(int $stateId): Response
    {
        $addressService = new AddressService();
        $states = $addressService->getCitiesByStates($stateId);

        return new Response($states, 200);
    }
}
