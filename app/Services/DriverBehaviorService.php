<?php

namespace App\Services;

use App\Models\DriverBehavior;

class DriverBehaviorService
{
    public function storeBehavior(array $data): DriverBehavior
    {
        $behavior = DriverBehavior::firstOrNew([
            'driver_id' => $data['driver_id'],
            'trip_id' => $data['trip_id'],
        ]);

        $behavior->speeding_instances += $data['speeding_instances'] ?? 0;
        $behavior->harsh_braking_instances += $data['harsh_braking_instances'] ?? 0;
        $behavior->max_speed = max($behavior->max_speed, $data['max_speed'] ?? 0);

        if (isset($data['speeding_location'])) {
            $locations = $behavior->speeding_locations ?? [];
            $locations[] = $data['speeding_location'];
            $behavior->speeding_locations = $locations;
        }

        if (isset($data['harsh_braking_location'])) {
            $locations = $behavior->harsh_braking_locations ?? [];
            $locations[] = $data['harsh_braking_location'];
            $behavior->harsh_braking_locations = $locations;
        }

        $behavior->save();

        return $behavior;
    }
}
