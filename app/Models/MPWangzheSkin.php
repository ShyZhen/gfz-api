<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MPWangzheSkin extends Model
{
    //
    protected $table = 'wangzhe_skin';

    protected $fillable = [
        'user_id', 'skin_patch',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
