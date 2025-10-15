<?php

namespace App\Models;

use App\Models\Item;
use App\Models\User;
use App\Models\ProductUsages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUsageDetail extends Model
{
    protected $guarded = ['id'];

    /**
     * Get the product usage that owns the detail
     */
    public function productUsage(): BelongsTo
    {
        return $this->belongsTo(ProductUsages::class, 'product_usage_id');
    }

    /**
     * Get the item that owns the detail
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the employee that owns the detail
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the user that created the detail
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
