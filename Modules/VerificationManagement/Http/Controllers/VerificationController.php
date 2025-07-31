<?php

namespace Modules\VerificationManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\DriverDetails;

class VerificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('verificationmanagement::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('verificationmanagement::create');
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
        return view('verificationmanagement::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('verificationmanagement::edit');
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

    public function verifyUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_verified' => 'required|boolean',
        ]);

        $user = User::find($request->user_id);
        $user->is_verified = $request->is_verified;
        $user->save();

        return response()->json(['message' => 'User verification status updated successfully.']);
    }

    public function verifyDriver(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:driver_details,id',
            'is_verified' => 'required|boolean',
        ]);

        $driver = DriverDetails::find($request->driver_id);
        $driver->is_verified = $request->is_verified;
        $driver->save();

        return response()->json(['message' => 'Driver verification status updated successfully.']);
    }

    public function setChildFriendly(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:driver_details,id',
            'is_child_friendly' => 'required|boolean',
        ]);

        $driver = DriverDetails::find($request->driver_id);
        $driver->is_child_friendly = $request->is_child_friendly;
        $driver->save();

        return response()->json(['message' => 'Driver child friendly status updated successfully.']);
    }

    public function setNanny(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:driver_details,id',
            'is_nanny' => 'required|boolean',
        ]);

        $driver = DriverDetails::find($request->driver_id);
        $driver->is_nanny = $request->is_nanny;
        $driver->save();

        return response()->json(['message' => 'Driver nanny status updated successfully.']);
    }

    public function setKidsOnlyVerified(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:driver_details,id',
            'is_kids_only_verified' => 'required|boolean',
        ]);

        $driver = DriverDetails::find($request->driver_id);
        $driver->is_kids_only_verified = $request->is_kids_only_verified;
        $driver->save();

        return response()->json(['message' => 'Driver kids only verified status updated successfully.']);
    }

    public function setHasBabySeat(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:driver_details,id',
            'has_baby_seat' => 'required|boolean',
        ]);

        $driver = DriverDetails::find($request->driver_id);
        $driver->has_baby_seat = $request->has_baby_seat;
        $driver->save();

        return response()->json(['message' => 'Driver has baby seat status updated successfully.']);
    }

    public function setGender(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'gender' => 'required|string',
        ]);

        $user = User::find($request->user_id);
        $user->gender = $request->gender;
        $user->save();

        return response()->json(['message' => 'User gender updated successfully.']);
    }
}
