<?php

use App\Http\Controllers\API\RoomController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AccountController;
use APP\Http\Controllers\API\CustomerController;
use APP\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\ServiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Room Types
Route::get('/list-room-types', [RoomController::class, 'index']);
Route::get('/room-type/{id}', [RoomController::class, 'show']);
Route::get('/find-room-type', [RoomController::class, 'findRoomType']);
Route::get('/filter-room-type', [RoomController::class, 'filterRoomType']);

// Services
Route::get('/list-services', [ServiceController::class, 'index']);
Route::get('/service/{id}', [ServiceController::class, 'show']);

// Accounts
Route::get('accounts', [AccountController::class, 'index']);
Route::get('accounts/find/{username}', [AccountController::class, 'find']);

Route::get('customers', [AccountController::class, 'showCustomer']);
Route::get('/customer', [CustomerController::class, 'index']);
Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});

