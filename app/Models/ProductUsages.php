<?php

namespace App\Models;

use App\Models\Item;
use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUsages extends Model
{
    protected $guarded = ['id'];
    
    protected $fillable = [
        'item_id',
        'quantity',
        'usage_date',
        'notes',
        'user_id',
        'company_id',
        'del_status'
    ];

    protected $casts = [
        'usage_date' => 'date',
    ];

    /**
     * Get the item that owns the usage
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the user that created the usage
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the usage
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
