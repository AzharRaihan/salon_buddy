<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model
{
    protected $guarded = ['id'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function items()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function serviceSeller()
    {
        return $this->belongsTo(User::class, 'service_seller_id', 'id');
    }


    public function servicePackage()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}
