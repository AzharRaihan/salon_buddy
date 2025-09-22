<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    protected $appends = [
        'photo_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'loyalty_points' => 'decimal:3',
        ];
    }

    protected $guard_name = 'sanctum';

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && file_exists(public_path('assets/images/' . $this->photo))) {
            return asset('assets/images/' . $this->photo);
        }

        return asset('assets/images/system-config/default-picture.png');
    }

    /**
     * Get the bookings for the customer.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the full name of the customer.
     */
    public function getFullNameAttribute(): string
    {
        return $this->name ?? '';
    }

    public function packageUsagesSummary(): HasMany
    {
        return $this->hasMany(PackageUsagesSummary::class);
    }

    public function customerReceives(): HasMany
    {
        return $this->hasMany(CustomerReceive::class, 'customer_id', 'id');
    }
}
