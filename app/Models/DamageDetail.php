<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageDetail extends Model
{
    protected $guarded = ['id'];

    public function damage()
    {
        return $this->belongsTo(Damage::class, 'damage_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

}