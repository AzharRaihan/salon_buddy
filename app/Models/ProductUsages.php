<?php

namespace App\Models;

use App\Models\User;
use App\Models\Branch;
use App\Models\Company;
use App\Models\ProductUsageDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUsages extends Model
{
    protected $guarded = ['id'];


    /**
     * Get the usage details for the product usage
     */
    public function productUsageDetails(): HasMany
    {
        return $this->hasMany(ProductUsageDetail::class, 'product_usage_id');
    }

    /**
     * Get the branch that owns the usage
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
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
