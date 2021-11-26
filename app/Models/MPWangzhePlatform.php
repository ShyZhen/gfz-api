<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MPWangzhePlatform extends Model
{
    //
    protected $table = 'wangzhe_platform';

    protected $fillable = [
        'uuid', 'app_id', 'app_secret', 'deleted',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
