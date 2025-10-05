<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'item_id',
        'employee_id',
        'unit_price',
        'quantity',
        'subtotal',
        'total_tax',
        'tax_breakdown',
        'total_payable',
        'promotion_discount',
        'is_free',
        'promotion_id',
        'loyalty_point_earn',
        'user_id',
        'branch_id',
        'company_id',
        'del_status'
    ];

    protected $casts = [
        'unit_price' => 'decimal:3',
        'quantity' => 'decimal:3',
        'subtotal' => 'decimal:3',
        'total_tax' => 'decimal:3',
        'total_payable' => 'decimal:3',
        'promotion_discount' => 'decimal:3',
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(User::class, 'assigned_employee_id');
    }

    // Scopes
    public function scopeLive($query)
    {
        return $query->where('del_status', 'Live');
    }

}
