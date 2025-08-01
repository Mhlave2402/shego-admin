<?php

namespace Modules\PromotionManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\PromotionManagement\Database\factories\CustomerLevelCouponSetupFactory;
use Modules\UserManagement\Entities\UserLevel;

class CustomerLevelCouponSetup extends Model
{
    use HasFactory;

    protected $fillable = ['user_level_id', 'coupon_setup_id'];


    public function userLevel()
    {
        return $this->belongsTo(UserLevel::class);
    }

    public function coupon()
    {
        return $this->belongsTo(CouponSetup::class);
    }

    protected static function newFactory(): CustomerLevelCouponSetupFactory
    {
        //return CustomerLevelCouponSetupFactory::new();
    }
}
