<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API;

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


//Route::get('user', [API\ApiController::class,'user'])->middleware('auth:api');
Route::group([
    'prefix' => 'requests',
//    'middleware' => 'auth:api'
], function () {
    Route::post('register', [API\ApiController::class, 'register']);
    Route::group([
        'middleware' => ['auth:api', 'check.role']
    ], function () {
        Route::get('', [API\ApiController::class, 'getApplication']);
        Route::post('',[API\ApiController::class, 'sendApplication']);
        Route::put('{id}', [API\ApiController::class, 'aplicationResponce']);
    });
});
