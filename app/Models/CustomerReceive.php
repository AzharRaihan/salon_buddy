<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReceive extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reference_no',
        'date',
        'amount',
        'note',
        'customer_id',
        'payment_method_id',
        'branch_id',
        'user_id',
        'company_id',
        'del_status'
    ];

    /**
     * Get the customer that owns the receive.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * Get the payment method that owns the receive.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }

    /**
     * Get the branch that owns the receive.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user that owns the receive.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the receive.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
