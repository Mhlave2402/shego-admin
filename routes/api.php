<?php

use Illuminate\Http\Request;
use App\Http\Controllers\FeatureFeeController;
use App\Http\Controllers\SplitPaymentController;
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

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('split-payment/initiate', [SplitPaymentController::class, 'initiateSplit']);
    Route::post('split-payment/{splitPayment}/respond', [SplitPaymentController::class, 'respondToSplit']);
});

Route::group(['prefix' => 'admin', 'middleware' => ['auth:api', 'role:admin']], function () {
    Route::apiResource('feature-fees', FeatureFeeController::class);
});
