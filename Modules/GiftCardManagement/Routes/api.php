<?php

use Illuminate\Support\Facades\Route;
use Modules\GiftCardManagement\Http\Controllers\GiftCardController;

Route::group(['prefix' => 'admin', 'middleware' => ['auth:api', 'role:admin']], function () {
    Route::apiResource('gift-cards', GiftCardController::class);
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('gift-cards/redeem', [GiftCardController::class, 'redeem']);
});
