<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wiki extends Model
{
    //
    protected $table = 'wiki';

    protected $fillable = [
        'title', 'formula', 'bio', 'content', 'extra',
    ];
}
