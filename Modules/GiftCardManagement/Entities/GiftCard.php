<?php

namespace Modules\GiftCardManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiftCard extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'code',
        'amount',
        'status',
        'user_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        //return \Modules\GiftCardManagement\Database\factories\GiftCardFactory::new();
    }
}
