<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    public function buyItem()
    {
        return $this->belongsTo(Item::class, 'buy_item_id');
    }

    public function getItem()
    {
        return $this->belongsTo(Item::class, 'get_item_id');
    }

    public function discountItem()
    {
        return $this->belongsTo(Item::class, 'discount_item_id');
    }

}
