<?php

namespace App\Jobs;

use App\Events\CustomerTripRequestEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\TripManagement\Service\Interface\TempTripNotificationServiceInterface;
use Modules\TripManagement\Service\Interface\TripRequestServiceInterface;

class ProcessPushNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $radius;
    protected $trip;
    protected $parcelWeight;
    protected $has_baby_seat;
    protected $gender;
    public function __construct( $radius, $trip, $parcelWeight = null, $has_baby_seat = false, $gender = null)
    {
        $this->radius = $radius;
        $this->trip = $trip;
        $this->parcelWeight = $parcelWeight;
        $this->has_baby_seat = $has_baby_seat;
        $this->gender = $gender;
    }


    public function handle(): void
    {
        $tripRequestService = app()->make(TripRequestServiceInterface::class);
        $tempTripNotificationService = app()->make(TempTripNotificationServiceInterface::class);
        $find_drivers = $tripRequestService->findNearestDrivers(
            latitude: $this->trip->coordinate->pickup_coordinates->latitude,
            longitude: $this->trip->coordinate->pickup_coordinates->longitude,
            zoneId: $this->trip->zone_id,
            radius: $this->radius,
            vehicleCategoryId: $this->trip->vehicle_category_id,
            requestType: $this->trip->type,
            rideRequestType: $this->trip->ride_request_type,
            parcelWeight: $this->parcelWeight,
            has_baby_seat: $this->has_baby_seat,
            gender: $this->gender
        );

        if (!empty($find_drivers)) {
            $notify = [];
            foreach ($find_drivers as $key => $value) {
                if ($value->user?->fcm_token) {
                    $notify[$key]['user_id'] = $value->user->id;
                    $notify[$key]['trip_request_id'] = $this->trip->id;
                }
            }
            $requestType = $this->trip->type == PARCEL ? 'parcel_request' : (
                $this->trip->ride_request_type == SCHEDULED ? 'schedule_trip_request' : 'ride_request'
            );
            $push = getNotification('new_' . $requestType);
            $notification = [
                'title' => $push['title'],
                'description' => $push['description'],
                'status' => $push['status'],
                'ride_request_id' => $this->trip->id,
                'type' => $this->trip->type,
                'notification_type' => $this->trip->type == RIDE_REQUEST ? 'trip' : 'parcel',
                'action' => $push['action'],
                'replace' => [
                    'tripId' => $this->trip->ref_id,
                    'parcelId' => $this->trip->parcel_id,
                    'approximateAmount' => getCurrencyFormat($this->trip->estimated_fare),
                    'dropOffLocation' => $this->trip->coordinate->destination_address,
                    'pickUpLocation' => $this->trip->coordinate->pickup_address
                ],
            ];
            if(!empty($notify)){
                $tempTripNotificationService->createMany($notify);
                dispatch(new SendPushNotificationJob($notification, $find_drivers))->onQueue('high');
            }
            if (checkReverbConnection()) {
                foreach ($find_drivers as $key => $value) {
                    CustomerTripRequestEvent::broadcast($value->user, $this->trip);
                }
            }
            if (!is_null(businessConfig('server_key', NOTIFICATION_SETTINGS))) {
                sendTopicNotification(
                    'admin_notification',
                    translate('new_request_notification'),
                    translate('new_request_has_been_placed'),
                    'null',
                    $this->trip->id,
                    $this->trip->type);
            }
        }
    }
}
