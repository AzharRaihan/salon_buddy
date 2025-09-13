<?php

namespace App\Models;

use App\Models\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemDetail extends Model
{
    protected $guarded = ['id'];

    public function items()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_relation_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

}
