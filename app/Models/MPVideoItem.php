<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MPVideoItem extends Model
{
    //
    protected $table = 'mp_video_items';

    protected $fillable = [
        'vid', 'type', 'image', 'title', 'desc', 'vip_type',
    ];

}
