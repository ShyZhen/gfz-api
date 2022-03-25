<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MPWangzheHeroTutorial extends Model
{
    //
    protected $table = 'wangzhe_hero_tutorial';

    protected $fillable = [
        'hero_id', 'ming', 'ming_tips', 'equipment', 'equipment_tips', 'counter_hero'
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getMingAttribute()
    {
        return $this->attributes['ming'] = json_decode($this->attributes['ming'], true);
    }

    public function getEquipmentAttribute()
    {
        return $this->attributes['equipment'] = json_decode($this->attributes['equipment'], true);
    }

    public function getCounterHeroAttribute()
    {
        return $this->attributes['counter_hero'] = json_decode($this->attributes['counter_hero'], true);
    }

}
