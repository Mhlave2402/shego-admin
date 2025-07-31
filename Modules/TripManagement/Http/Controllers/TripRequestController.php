<?php

namespace Modules\TripManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Modules\TripManagement\Entities\TripRequest;
use App\Models\SosAlert;

class TripRequestController extends Controller
{
    protected $tripRequest;

    public function __construct(TripRequest $tripRequest)
    {
        $this->tripRequest = $tripRequest;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('tripmanagement::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('tripmanagement::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function updateHasMaleCompanion(Request $request)
    {
        $request->validate([
            'trip_request_id' => 'required|exists:trip_requests,id',
            'has_male_companion' => 'required|boolean',
        ]);

        $tripRequest = TripRequest::find($request->trip_request_id);
        $tripRequest->has_male_companion = $request->has_male_companion;
        $tripRequest->save();

        return response()->json(['message' => 'Trip request has male companion status updated successfully.']);
    }

    public function createSosAlert(Request $request, $id)
    {
        $trip = $this->tripRequest->find($id);
        if ($trip) {
            $trip->update(['is_sos_on' => true]);
            SosAlert::create([
                'trip_request_id' => $trip->id,
                'user_id' => auth()->id(),
                'sent_by' => auth()->user()->user_type,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
            return response()->json(['message' => 'SOS alert sent successfully']);
        }
        return response()->json(['message' => 'Trip not found'], 404);
    }

    public function shareTrip(Request $request, $id)
    {
        $trip = $this->tripRequest->find($id);
        if ($trip) {
            $link = URL::temporarySignedRoute(
                'trip.track', now()->addHours(24), ['trip' => $trip->id]
            );
            $trip->update(['live_trip_sharing_link' => $link]);
            return response()->json(['link' => $link]);
        }
        return response()->json(['message' => 'Trip not found'], 404);
    }
}
