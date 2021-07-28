<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MPWangzheDrawUser extends Model
{
    //
    protected $table = 'wangzhe_draws_user';

    protected $fillable = [
        'user_id', 'draw_id',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
