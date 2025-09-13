<?php

namespace App\Models;

use App\Models\Unit;
use App\Models\ItemDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
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

    public function itemDetails(): HasMany
    {
        return $this->hasMany(ItemDetail::class, 'item_relation_id', 'id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function bookingDetails(): HasMany
    {
        return $this->hasMany(BookingDetail::class, 'item_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Ratting::class, 'item_id', 'id');
    }

    public function averageRating()
    {
        return $this->ratings()->where('del_status', 'Live')->avg('rating') ?? 0;
    }

    public function totalReviews()
    {
        return $this->ratings()->where('del_status', 'Live')->count();
    }

    public function packageUsagesSummary(): HasMany
    {
        return $this->hasMany(PackageUsagesSummary::class);
    }

    public function productUsages(): HasMany
    {
        return $this->hasMany(ProductUsages::class, 'item_id', 'id');
    }


    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class, 'item_id', 'id');
        return $this->hasMany(Promotion::class, 'buy_item_id', 'id');
        return $this->hasMany(Promotion::class, 'get_item_id', 'id');
        return $this->hasMany(Promotion::class, 'discount_item_id', 'id');
    }


    public function damageDetails(): HasMany
    {
        return $this->hasMany(DamageDetail::class, 'item_id', 'id');
    }


    



}
