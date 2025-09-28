<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $guarded = ['id'];

    protected $appends = [
        'logo_url',
    ];

    public function getLogoUrlAttribute()
    {
        return asset('assets/images/' . $this->logo);
    }
}
