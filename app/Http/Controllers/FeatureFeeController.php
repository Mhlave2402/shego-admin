<?php

namespace App\Http\Controllers;

use App\Models\FeatureFee;
use Illuminate\Http\Request;

class FeatureFeeController extends Controller
{
    public function index()
    {
        return FeatureFee::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:feature_fees',
            'amount' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        return FeatureFee::create($request->all());
    }

    public function show(FeatureFee $featureFee)
    {
        return $featureFee;
    }

    public function update(Request $request, FeatureFee $featureFee)
    {
        $request->validate([
            'name' => 'string|unique:feature_fees,name,' . $featureFee->id,
            'amount' => 'numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $featureFee->update($request->all());

        return $featureFee;
    }

    public function destroy(FeatureFee $featureFee)
    {
        $featureFee->delete();

        return response()->json(null, 204);
    }
}
