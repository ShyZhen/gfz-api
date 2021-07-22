<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MPWangzheDraw extends Model
{
    //
    protected $table = 'wangzhe_draws';

    protected $fillable = [
        'limit_user', 'title', 'image', 'winner_id', 'type'
    ];

}
