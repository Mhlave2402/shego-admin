<?php

namespace App\Http\Controllers\Api;

use App\Events\StoreDriverBehavior;
use App\Http\Controllers\Controller;
use App\Models\DriverBehavior;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DriverBehaviorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|uuid|exists:users,id',
            'trip_id' => 'required|uuid|exists:trip_requests,id',
            'speeding_instances' => 'sometimes|integer',
            'harsh_braking_instances' => 'sometimes|integer',
            'max_speed' => 'sometimes|numeric',
            'speeding_location' => 'sometimes|array',
            'harsh_braking_location' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        StoreDriverBehavior::dispatch($request->all());

        return response()->json(['message' => 'Driver behavior data is being processed.'], 202);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $driver_id)
    {
        $driverBehavior = DriverBehavior::where('driver_id', $driver_id)->get();

        return response()->json($driverBehavior);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
