<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingProcess extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    protected $appends = [
        'photo_url',
    ];

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return asset('assets/images/' . $this->photo);
        }

        return asset('assets/images/system-config/default-picture.png');
    }
}
