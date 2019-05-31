<?php

use Illuminate\Http\Request;

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

Route::group([
    'prefix' => 'v1'
], function () {
    Route::get('/nearby/{code}','API@closeLocation')->name('nearLocation');
    Route::post('/new','API@newLocation')->name('newLocation');
    Route::post('/cashback','API@cashBack')->name('cashBack');
    Route::group([
    'middleware' => 'auth:api'
    ], function() {
      
    });
});
    Route::group([
        'prefix' => 'auth'
    ], function () {

    Route::group([
      'middleware' => 'auth:api'
    ], function() {
      
    });
});