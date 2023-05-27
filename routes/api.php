<?php

use App\Http\Controllers\PassportAuthController;
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

Route::post('/register', [PassportAuthController::class, 'register']);
Route::post('/login', [PassportAuthController::class, 'login']);

Route::group(['middleware' => 'auth:api'], function(){

    //User Authenticate
    Route::post('/logout', [PassportAuthController::class, 'logout']);
    Route::get('/user', [PassportAuthController::class, 'show']);
    Route::put('/reset', [PassportAuthController::class, 'resetPassword']);
});