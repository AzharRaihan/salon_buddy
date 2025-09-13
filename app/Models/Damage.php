<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Damage extends Model
{

    protected $guarded = ['id'];

    public function damageDetails()
    {
        return $this->hasMany(DamageDetail::class, 'damage_id', 'id');
    }
    
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    
}
