<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MPWangzheDraw extends Model
{
    //
    protected $table = 'wangzhe_draws';

    protected $fillable = [
        'platform_id', 'limit_user', 'title', 'image', 'winner_id', 'type'
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
