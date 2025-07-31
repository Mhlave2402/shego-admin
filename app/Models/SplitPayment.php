<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\TripManagement\Entities\TripRequest;

class SplitPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_request_id',
        'user_id',
        'amount',
        'status',
    ];

    public function tripRequest()
    {
        return $this->belongsTo(TripRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
