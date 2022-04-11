<?php

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

// AUTH CONTROLLER //
Route::group(['prefix' => 'auth', 'namespace' => 'App\Http\Controllers\Auth'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('recover_password', 'AuthController@passwordRecovery');
    Route::post('reset_password', 'AuthController@resetPassword');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
        Route::post('change_password', 'AuthController@resetPassword');
    });
});

// MAIN ROUTES //
Route::group(
    ['namespace' => 'App\Http\Controllers\Api', 'middleware' => ['auth:api']],
    function () {

        // CLIENTS //
        Route::get('clients/all', 'ClientController@indexAll');
        Route::get('clients/{id}/audits', 'ClientController@audits');
        Route::patch('clients/{id}/restore', 'ClientController@restore');
        Route::resource('clients', 'ClientController');

        // CLIENT MIDDLEWARE //
        Route::group(['middleware' => ['client']], function () {
            Route::get('addresses/all', 'AddressController@indexAll');
            Route::get('addresses/{id}/audits', 'AddressController@audits');
            Route::patch('addresses/{id}/restore', 'AddressController@restore');
            Route::resource('addresses', 'AddressController');
        });
    }
);
