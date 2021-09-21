<?php

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

Route::get('/user', [UserController::class, 'getAllUsers']);
Route::post('/user', [UserController::class, 'createUser']);
Route::get('/user/{id}', [UserController::class, 'getUser'])->where('id', '[0-9]+');
Route::put('/user/{id}', [UserController::class, 'updateUser'])->where('id', '[0-9]+');
Route::delete('/user/{id}', [UserController::class, 'deleteUser'])->where('id', '[0-9]+');
