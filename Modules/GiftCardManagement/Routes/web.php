<?php

use Illuminate\Support\Facades\Route;
use Modules\GiftCardManagement\Http\Controllers\Web\New\Admin\GiftCardController;

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'], function () {
    Route::resource('gift-card', GiftCardController::class);
});
