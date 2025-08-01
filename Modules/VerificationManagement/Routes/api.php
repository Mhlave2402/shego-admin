<?php

use Illuminate\Support\Facades\Route;
use Modules\VerificationManagement\Http\Controllers\VerificationController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('verification/user', [VerificationController::class, 'verifyUser']);
    Route::post('verification/driver', [VerificationController::class, 'verifyDriver']);
    Route::post('verification/driver/child-friendly', [VerificationController::class, 'setChildFriendly']);
    Route::post('verification/driver/kids-only-verified', [VerificationController::class, 'setKidsOnlyVerified']);
    Route::post('verification/driver/has-baby-seat', [VerificationController::class, 'setHasBabySeat']);
    Route::post('verification/user/gender', [VerificationController::class, 'setGender']);
});
