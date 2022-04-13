<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TianyitianRedpackage extends Model
{
    protected $table = 'tianyitian_redpackage';

    protected $fillable = [
        'title', 'url', 'key', 'is_deleted',
    ];
}
