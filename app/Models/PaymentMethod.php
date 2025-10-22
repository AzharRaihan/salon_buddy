<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    protected $appends = [
        'payment_method_icon_url',
    ];

    public function getPaymentMethodIconUrlAttribute(): string
    {
        if ($this->payment_method_icon && file_exists(public_path('assets/images/' . $this->payment_method_icon))) {
            return asset('assets/images/' . $this->payment_method_icon);
        }
        return asset('assets/images/system-config/default-picture.png');
    }

    public function customerReceives(): HasMany
    {
        return $this->hasMany(CustomerReceive::class, 'payment_method_id', 'id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'payment_method_id', 'id');
    }

    public function supplierPayments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class, 'payment_method_id', 'id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'payment_method_id', 'id');
    }

    public function salaryPayments(): HasMany
    {
        return $this->hasMany(SalaryPayment::class, 'payment_method_id', 'id');
    }

    public function staffPayments(): HasMany
    {
        return $this->hasMany(StaffPayment::class, 'payment_method_id', 'id');
    }

    public function depositWithdraws(): HasMany
    {
        return $this->hasMany(DepositWithdraw::class, 'payment_method_id', 'id');
    }

}
