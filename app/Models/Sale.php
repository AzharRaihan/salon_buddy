<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'order_date',
        'order_update_date',
        'order_from',
        'order_status',
        'total_tax',
        'tax_breakdown',
        'total_payable',
        'total_paid',
        'total_due',
        'subtotal_without_tax_discount',
        'grandtotal_with_tax_discount',
        'discount',
        'delivery_charge',
        'delivery_area_id',
        'promotion_discount',
        'customer_id',
        'payment_method_id',
        'transaction_id',
        'branch_id',
        'user_id',
        'company_id',
        'loyalty_points_earned',
        'loyalty_points_redeemed',
        'loyalty_points_value',
        'del_status'
    ];

    protected $casts = [
        'total_tax' => 'decimal:3',
        'total_payable' => 'decimal:3',
        'promotion_discount' => 'decimal:3',
        'loyalty_points_earned' => 'decimal:2',
        'loyalty_points_redeemed' => 'decimal:2',
        'loyalty_points_value' => 'decimal:2',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    // Scopes
    public function scopeLive($query)
    {
        return $query->where('del_status', 'Live');
    }

    public function scopeWebsite($query)
    {
        return $query->where('order_from', 'Website');
    }

    public function scopePos($query)
    {
        return $query->where('order_from', 'POS');
    }
}
