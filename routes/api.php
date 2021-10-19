<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\HealthUnitController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/users', [UserController::class, 'getAllUsers']);
Route::post('/users', [UserController::class, 'createUser']);
Route::get('/users/{id}', [UserController::class, 'getUser'])
    ->where('id', '[0-9]+');
Route::put('/users/{id}', [UserController::class, 'updateUser'])
    ->where('id', '[0-9]+');
Route::delete('/users/{id}', [UserController::class, 'deleteUser'])
    ->where('id', '[0-9]+');
Route::get('states', [AddressController::class, 'getStates']);
Route::get('state-cities/{id}', [AddressController::class, 'getCitiesByState'])
    ->where('id', '[0-9]+');
Route::post('/health-units', [HealthUnitController::class, 'createHealthUnit']);
Route::put('/health-units/{id}', [HealthUnitController::class, 'updateHealthUnit'])
    ->where('id', '[0-9]+');
Route::get('/health-units/{id}', [HealthUnitController::class, 'getHealthUnit'])
    ->where('id', '[0-9]+');
Route::get('/health-units', [HealthUnitController::class, 'getAllHealthUnits']);
Route::delete('/health-units/{id}', [HealthUnitController::class, 'deleteHealthUnit'])
    ->where('id', '[0-9]+');
