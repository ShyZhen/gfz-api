<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MPWangzheSkinConvert extends Model
{
    //
    protected $table = 'wangzhe_skin_convert';

    protected $fillable = [
        'user_id', 'user_uuid', 'convert_num', 'status'
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
