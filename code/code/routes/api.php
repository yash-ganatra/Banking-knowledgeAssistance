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
#Route::any('verifypancard/{passport_num}', 'API\ServicesController@PancardVerification');
#Route::any('verifyaadharcard/{aadhar_num}', 'API\ServicesController@AadharVerification');
#Route::any('ispanvalid', 'API\ServicesController@panisvalid');
#Route::any('panapi', 'API\ServicesController@panapi');
#Route::any('dedupe', 'API\ServicesController@dedupe');
#Route::middleware('auth:api')->get('/user', function (Request $request) {
#    return $request->user();
#});
