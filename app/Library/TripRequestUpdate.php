<?php


use App\Events\CustomerTripPaymentSuccessfulEvent;
use Modules\TripManagement\Entities\TripRequest;
use Modules\TransactionManagement\Traits\TransactionTrait;
use Modules\UserManagement\Lib\LevelHistoryManagerTrait;

if (!function_exists('tripRequestUpdate'))
{
    function tripRequestUpdate($data)
    {
        $trip = TripRequest::query()
            ->with(['driver', 'customer'])
            ->find($data->attribute_id);
        $trip->paid_fare = ($trip->paid_fare +$trip->tips);
        $trip->payment_status = PAID;
        $trip->save();
        $push = getNotification('payment_successful');
        sendDeviceNotification(
            fcm_token: $trip->driver->fcm_token,
            title: translate(key: $push['title'], locale: $trip?->driver?->current_language_key),
            description: textVariableDataFormat(value: $push['description'], paidAmount: $trip->paid_fare, methodName: translate(key: $trip->payment_method, locale: $trip?->driver?->current_language_key), locale: $trip?->driver?->current_language_key),
            status: $push['status'],
            ride_request_id: $trip->id,
            type: $trip->type,
            action: $push['action'],
            user_id: $trip->driver->id
        );
        if ($trip->tips > 0)
        {
            $pushTips = getNotification('tips_from_customer');
            sendDeviceNotification(
                fcm_token: $trip->driver->fcm_token,
                title: translate($pushTips['title']),
                description: translate(textVariableDataFormat(value: $pushTips['description'],tipsAmount: $trip->tips)),
                status: $push['status'],
                ride_request_id: $trip->id,
                type: $trip->type,
                action: $push['action'],
                user_id: $trip->driver->id
            );
        }
        if (!empty($trip)) {
            try {
                checkReverbConnection() && CustomerTripPaymentSuccessfulEvent::broadcast($trip);
            }catch(Exception $exception){

            }
        }

        (new class {
            use TransactionTrait;
        })->digitalPaymentTransaction($trip);

        return $trip;
    }
}
