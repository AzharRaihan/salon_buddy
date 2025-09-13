<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'website',
        'address',
        'currency',
        'currency_position',
        'precision',
        'thousand_separator',
        'decimal_separator',
        'date_format',
        'logo',
        'timezone',
    ];

    protected $appends = [
        'logo_url',
    ];

    public function getLogoUrlAttribute()
    {
        return asset('assets/images/' . $this->logo);
    }
}
