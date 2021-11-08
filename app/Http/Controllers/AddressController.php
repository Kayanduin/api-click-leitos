<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\State;
use App\Services\AddressService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AddressController extends Controller
{
    public function getStates(Request $request): Response
    {
        if ($request->user()->cannot('viewAny', State::class)) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $addressService = new AddressService();
        $states = $addressService->getStates();

        return new Response($states, 200);
    }

    public function getCitiesByState(Request $request, int $stateId): Response
    {
        if ($request->user()->cannot('viewAny', City::class)) {
            return new Response(['errors' => 'Access denied.'], 403);
        }

        $addressService = new AddressService();
        $states = $addressService->getCitiesByStates($stateId);

        return new Response($states, 200);
    }
}
