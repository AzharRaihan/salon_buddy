<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    protected $appends = [
        'photo_url',
    ];

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return asset('assets/images/' . $this->photo);
        }

        return asset('assets/images/default-images/branch.png');
    }

    public function packageUsagesSummary(): HasMany
    {
        return $this->hasMany(PackageUsagesSummary::class);
    }
}
