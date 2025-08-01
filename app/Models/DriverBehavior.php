<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverBehavior extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'trip_id',
        'speeding_instances',
        'harsh_braking_instances',
        'max_speed',
        'speeding_locations',
        'harsh_braking_locations',
    ];

    protected $casts = [
        'speeding_locations' => 'array',
        'harsh_braking_locations' => 'array',
    ];
}
