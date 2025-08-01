<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ride_request_id',
        'title',
        'description',
        'type',
        'notification_type',
        'action',
        'is_read',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    protected static function newFactory()
    {
        return \Modules\UserManagement\Database\factories\AppNotificationFactory::new();
    }
}
