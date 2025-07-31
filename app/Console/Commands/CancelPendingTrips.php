<?php

namespace App\Console\Commands;

use App\Jobs\SendPushNotificationJob;
use Illuminate\Console\Command;
use Modules\TripManagement\Entities\TempTripNotification;
use Modules\TripManagement\Entities\TripRequest;

class CancelPendingTrips extends Command
{
    protected $signature = 'trip-request:cancel';
    protected $description = 'Auto Cancel Pending Trip after certain period';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $activeMinutes = now()->subMinutes(get_cache('trip_request_active_time') ?? 10);
        $pendingTripRequests = TripRequest::whereIn('current_status', [PENDING])
            ->whereNull('scheduled_at')
            ->where('updated_at', '<', $activeMinutes)
            ->get();
        foreach ($pendingTripRequests as $pendingTripRequest) {
            $data = TempTripNotification::with('user')->where('trip_request_id', $pendingTripRequest->id)->get();
            $tripType = $pendingTripRequest->type == RIDE_REQUEST ? 'trip' : PARCEL;
            $push = getNotification($tripType . '_canceled');
            sendDeviceNotification(fcm_token: $pendingTripRequest->customer->fcm_token,
                title: translate(key: $push['title'], locale: $pendingTripRequest->customer?->current_language_key),
                description: textVariableDataFormat(value: $push['description'], tripId: $pendingTripRequest->ref_id, parcelId: $pendingTripRequest->ref_id, locale: $pendingTripRequest->customer?->current_language_key),
                status: $push['status'],
                ride_request_id: $pendingTripRequest->id,
                type: $pendingTripRequest->type,
                notification_type: $pendingTripRequest->type == RIDE_REQUEST ? 'trip' : 'parcel',
                action: $push['action'],
                user_id: $pendingTripRequest->customer->id
            );
            if (!empty($data)) {
                $notification = [
                    'title' => $push['title'],
                    'description' => $push['description'],
                    'status' => $push['status'],
                    'ride_request_id' => $pendingTripRequest->id,
                    'type' => $pendingTripRequest->type,
                    'notification_type' => $pendingTripRequest->type == RIDE_REQUEST ? 'trip' : 'parcel',
                    'action' => $push['action'],
                    'replace' => ['tripId' => $pendingTripRequest?->ref_id, 'sentTime' => pushSentTime($pendingTripRequest->updated_at)]
                ];
                dispatch(new SendPushNotificationJob($notification, $data))->onQueue('high');
                TempTripNotification::where('trip_request_id', $pendingTripRequest->id)->delete();
            }
        }
        TripRequest::whereIn('current_status', [PENDING])
            ->whereNull('scheduled_at')
            ->where('updated_at', '<', $activeMinutes)->update([
                'current_status' => 'cancelled',
            ]);
    }
}
