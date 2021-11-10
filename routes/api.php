<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\HealthUnitController;
use App\Http\Controllers\SamuController;
use App\Http\Controllers\UserController;
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

Route::post('/login', [AuthController::class, 'login']);

Route::post('/create-first-user', [UserController::class, 'createFirstUser'])->middleware('firstUser');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'firstLogin'])->group(function () {
    Route::post('/health-units', [HealthUnitController::class, 'createHealthUnit']);
    Route::get('states', [AddressController::class, 'getStates']);
    Route::get('state-cities/{id}', [AddressController::class, 'getCitiesByState'])
        ->where('id', '[0-9]+');
    Route::post('/samu-unit', [SamuController::class, 'create']);

    Route::middleware(['ValidateUserUnit'])->group(function () {
        Route::get('/users', [UserController::class, 'getAllUsers']);
        Route::post('/users', [UserController::class, 'createUser']);
        Route::get('/users/{id}', [UserController::class, 'getUser'])
            ->where('id', '[0-9]+');
        Route::put('/users/{id}', [UserController::class, 'updateUser'])
            ->where('id', '[0-9]+');
        Route::delete('/users/{id}', [UserController::class, 'deleteUser'])
            ->where('id', '[0-9]+');
        Route::put('/health-units/{id}', [HealthUnitController::class, 'updateHealthUnit'])
            ->where('id', '[0-9]+');
        Route::get('/health-units/{id}', [HealthUnitController::class, 'getHealthUnit'])
            ->where('id', '[0-9]+');
        Route::get('/health-units', [HealthUnitController::class, 'getAllHealthUnits']);
        Route::delete('/health-units/{id}', [HealthUnitController::class, 'deleteHealthUnit'])
            ->where('id', '[0-9]+');
        Route::get('/health-unit-with-beds', [HealthUnitController::class, 'getAllHealthUnitsWithBeds']);
        Route::get('/beds/{id}', [BedController::class, 'getBedById'])
            ->where('id', '[0-9]+');
        Route::get('health-unit-beds/{id}', [BedController::class, 'getBedsByHealthUnit'])
            ->where('id', '[0-9]+');
        Route::post('/beds', [BedController::class, 'createBed']);
        Route::put('/beds/{id}', [BedController::class, 'updateBed'])
            ->where('id', '[0-9]+');
        Route::delete('/beds/{id}', [BedController::class, 'deleteBed'])
            ->where('id', '[0-9]+');
        Route::post('/increase-free-beds/{id}', [BedController::class, 'increaseFreeBeds'])
            ->where('id', '[0-9]+');
        Route::post('/decrease-free-beds/{id}', [BedController::class, 'decreaseFreeBeds'])
            ->where('id', '[0-9]+');
        Route::get('/bed-types', [BedController::class, 'getBedTypes']);
        Route::get('/user-roles', [UserController::class, 'getUserRoles']);
        Route::get('/samu-unit/{id}', [SamuController::class, 'view'])
            ->where('id', '[0-9]+');
        Route::put('/samu-unit/{id}', [SamuController::class, 'update'])
            ->where('id', '[0-9]+');
        Route::delete('/samu-unit/{id}', [SamuController::class, 'delete'])
            ->where('id', '[0-9]+');
    });
});
