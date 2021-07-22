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

}
