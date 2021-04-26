<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportApp extends Model
{
    //
    protected $table = 'report_apps';

    protected $fillable = [
        'content', 'user_id', 'poster_list',
    ];
}
