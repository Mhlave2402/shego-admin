<?php

use App\Http\Controllers\DemoController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ParcelTrackingController;
use App\Http\Controllers\PaymentRecordController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Modules\TripManagement\Entities\TripRequest;
use Pusher\Pusher;
use Pusher\PusherException;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/sender', function () {
    return event(new App\Events\NewMessage("hello"));
});

Route::controller(LandingPageController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/contact-us', 'contactUs')->name('contact-us');
    Route::get('/about-us', 'aboutUs')->name('about-us');
    Route::get('/privacy', 'privacy')->name('privacy');
    Route::get('/terms', 'terms')->name('terms');
    Route::get('/test-connection', function () {
        $trip = TripRequest::first();
        if (areAllBroadcastServicesRunning()){
            \App\Events\CustomerTripRequestEvent::broadcast($trip->driver, $trip);
            return true;
        }else{
            dd("not broadcast");
        }
    });
});
Route::get('track-parcel/{id}', [ParcelTrackingController::class, 'trackingParcel'])->name('track-parcel');

Route::get('add-payment-request', [PaymentRecordController::class, 'index']);

Route::get('payment-success', [PaymentRecordController::class, 'success'])->name('payment-success');
Route::get('payment-fail', [PaymentRecordController::class, 'fail'])->name('payment-fail');
Route::get('payment-cancel', [PaymentRecordController::class, 'cancel'])->name('payment-cancel');
Route::get('/update-data-test', [DemoController::class, 'demo'])->name('demo');
Route::get('sms-test', [DemoController::class, 'smsGatewayTest'])->name('sms-test');
Route::get('firebase-gen', [DemoController::class, 'firebaseMessageConfigFileGen'])->name('firebase-gen');

Route::get('trigger', function () {
    broadcast(new \App\Events\SampleEvent('Hello'));
    return true;
});

Route::get('test', function () {
    sendTopicNotification(
        'admin_message',
        translate('new_request_notification'),
        translate('new_request_has_been_placed'),
        'null');
    return true;
});

Route::get('/trip/track/{trip}', function (Request $request, TripRequest $trip) {
    if (! $request->hasValidSignature()) {
        abort(401);
    }

    return view('track', ['trip' => $trip]);
})->name('trip.track');
