<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageUsagesSummary extends Model
{
    protected $fillable = [
        'customer_id',
        'package_id',
        'package_item_id',
        'usages_qty',
        'sale_id',
        'usages_date',
        'usages_time',
        'user_id',
        'company_id',
        'branch_id', // Added missing branch_id
    ];

    // Add relationship to Item (service used)
    public function item()
    {
        return $this->belongsTo(Item::class, 'package_item_id');
    }

    // Add relationship to Package (the package itself)
    public function package()
    {
        return $this->belongsTo(Item::class, 'package_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
