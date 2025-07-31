<?php

namespace App\Http\Controllers;

use App\Models\SplitPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\TripManagement\Entities\TripRequest;

class SplitPaymentController extends Controller
{
    public function initiateSplit(Request $request)
    {
        $request->validate([
            'trip_request_id' => 'required|exists:trip_requests,id',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $trip = TripRequest::find($request->trip_request_id);
        $user = User::find($request->user_id);

        if ($trip->paid_fare < $request->amount) {
            return response()->json(['message' => 'Split amount cannot be greater than the trip fare.'], 422);
        }

        $splitPayment = SplitPayment::create([
            'trip_request_id' => $trip->id,
            'user_id' => $user->id,
            'amount' => $request->amount,
        ]);

        // Here you would typically send a notification to the other user
        // to approve or reject the split payment request.

        return response()->json($splitPayment, 201);
    }

    public function respondToSplit(Request $request, SplitPayment $splitPayment)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        if ($splitPayment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $splitPayment->update(['status' => $request->status]);

        if ($request->status === 'accepted') {
            // Here you would typically process the payment for the user
            // who accepted the split.
        }

        return response()->json($splitPayment);
    }
}
