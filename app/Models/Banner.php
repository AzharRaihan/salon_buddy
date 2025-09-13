<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    protected $appends = [
        'banner_image_url',
    ];

    public function getBannerImageUrlAttribute(): string
    {
        if ($this->banner_image) {
            return asset('assets/images/' . $this->banner_image);
        }

        return asset('assets/images/default-images/hero-banner-1.png');
    }
}
