<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    //
    protected $table = 'coupons';

    protected $fillable = [
        'name', 'icon', 'banner_pic', 'url', 'app_id', 'path', 'origin_image'
    ];
}
