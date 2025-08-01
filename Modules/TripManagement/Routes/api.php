<?php

use Illuminate\Support\Facades\Route;
use Modules\TripManagement\Http\Controllers\Api\New\Customer\TripRequestController as NewCustomerTripController;
use Modules\TripManagement\Http\Controllers\Api\New\Driver\TripRequestController as NewDriverTripController;
use Modules\TripManagement\Http\Controllers\Api\New\Customer\ParcelRefundController;
use Modules\TripManagement\Http\Controllers\Api\New\Customer\SafetyAlertController;
use Modules\TripManagement\Http\Controllers\Api\New\PaymentController;

/**
 * CUSTOMER API LIST
 */

#### NEW CUSTOMER TRIP CONTROLLER ####
Route::group(['prefix' => 'customer', 'middleware' => ['auth:api', 'maintenance_mode']], function () {
    Route::get('drivers-near-me', [NewCustomerTripController::class, 'driversNearMe']);
    Route::group(['prefix' => 'ride'], function () {
        Route::controller(NewCustomerTripController::class)->group(function () {
            Route::post('get-estimated-fare', 'getEstimatedFare');
            Route::post('create', 'createRideRequest');
            Route::put('ignore-bidding', 'ignoreBidding');
            Route::get('bidding-list/{trip_request_id}', 'biddingList');
            Route::put('update-status/{trip_request_id}', 'rideStatusUpdate');
            Route::get('details/{trip_request_id}', 'rideDetails');
            Route::get('list', 'rideList');
            Route::get('final-fare', 'finalFareCalculation');
            Route::post('trip-action', 'requestAction');
            Route::get('ride-resume-status', 'rideResumeStatus');
            Route::put('arrival-time', 'arrivalTime');
            Route::put('coordinate-arrival', 'coordinateArrival');
            Route::get('ongoing-parcel-list', 'pendingParcelList');
            Route::get('unpaid-parcel-list', 'unpaidParcelRequest');
            Route::put('received-returning-parcel/{trip_request_id}', 'receivedReturningParcel');

            // edit schedule trip
            Route::put('edit-scheduled-trip/{trip_request_id}', 'editScheduledTrip');
            // pending ride list
            Route::get('pending-ride-list', 'pendingRideList');
        });
        Route::post('track-location', [NewDriverTripController::class, 'trackLocation']);
        Route::get('payment', [PaymentController::class, 'payment']);
        Route::get('digital-payment', [PaymentController::class, 'digitalPayment'])->withoutMiddleware('auth:api');
    });
    Route::group(['prefix' => 'parcel'], function () {
        Route::controller(ParcelRefundController::class)->group(function () {
            Route::group(['prefix' => 'refund'], function () {
                Route::post('create', 'createParcelRefundRequest');
            });
        });
    });
    Route::group(['prefix' => 'safety-alert'], function () {
        Route::controller(SafetyAlertController::class)->group(function () {
            Route::post('store', 'storeSafetyAlert');
            Route::put('resend/{trip_request_id}', 'resendSafetyAlert');
            Route::put('mark-as-solved/{trip_request_id}', 'markAsSolvedSafetyAlert');
            Route::get('show/{trip_request_id}', 'showSafetyAlert');
            Route::delete('undo/{trip_request_id}', 'deleteSafetyAlert');
        });
    });
});


/**
 * DRIVER API LIST
 */
Route::group(['prefix' => 'driver', 'middleware' => ['auth:api', 'maintenance_mode']], function () {
    Route::get('last-ride-details', [NewDriverTripController::class, 'lastRideDetails']);
    Route::group(['prefix' => 'ride', 'middleware' => ['auth:api', 'maintenance_mode']], function () {
        Route::get('final-fare', [NewCustomerTripController::class, 'finalFareCalculation']);
        Route::get('payment', [PaymentController::class, 'payment']);


        #### NEW DRIVER TRIP CONTROLLER ####
        Route::controller(NewDriverTripController::class)->group(function () {
            Route::get('show-ride-details', 'showRideDetails');
            Route::get('all-ride-list', 'allRideList');
            Route::put('ride-waiting', 'rideWaiting');
            Route::get('list', 'rideList');
            Route::put('arrival-time', 'arrivalTime');
            Route::put('coordinate-arrival', 'coordinateArrival');
            Route::get('ongoing-parcel-list', 'pendingParcelList');
            Route::get('unpaid-parcel-list', 'unpaidParcelRequest');
            Route::put('resend-otp', 'resendOtp');
            Route::post('match-otp', 'matchOtp');
            Route::post('track-location', 'trackLocation');
            Route::get('details/{ride_request_id}', 'rideDetails');
            Route::get('pending-ride-list', 'pendingRideList');
            Route::put('returned-parcel', 'returnedParcel');
            Route::get('overview', 'tripOverview');
            Route::post('ignore-trip-notification', 'ignoreTripNotification');
            Route::put('update-status', 'rideStatusUpdate');
            Route::post('trip-action', 'requestAction');
            Route::post('bid', 'bid');
            Route::put('update-to-out-for-pickup/{tripId}', 'updateToOutForPickup');
        });
    });
    Route::group(['prefix' => 'safety-alert'], function () {
        Route::controller(\Modules\TripManagement\Http\Controllers\Api\New\Driver\SafetyAlertController::class)->group(function () {
            Route::post('store', 'storeSafetyAlert');
            Route::put('resend/{trip_request_id}', 'resendSafetyAlert');
            Route::put('mark-as-solved/{trip_request_id}', 'markAsSolvedSafetyAlert');
            Route::get('show/{trip_request_id}', 'showSafetyAlert');
            Route::delete('undo/{trip_request_id}', 'deleteSafetyAlert');
        });
    });
});

Route::post('ride/store-screenshot', [NewDriverTripController::class, 'storeScreenshot'])->middleware('auth:api');
Route::post('ride/update-has-male-companion', [\Modules\TripManagement\Http\Controllers\TripRequestController::class, 'updateHasMaleCompanion'])->middleware('auth:api');
Route::post('ride/create-sos-alert', [\Modules\TripManagement\Http\Controllers\TripRequestController::class, 'createSosAlert'])->middleware('auth:api');
Route::post('ride/trip/{id}/share', [\Modules\TripManagement\Http\Controllers\TripRequestController::class, 'shareTrip'])->middleware('auth:api');
