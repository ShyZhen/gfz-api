<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MPWangzheSkinLog extends Model
{
    //
    protected $table = 'wangzhe_skin_log';

    protected $fillable = [
        'user_id', 'num', 'type',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
