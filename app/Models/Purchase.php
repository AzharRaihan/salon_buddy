<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    protected $appends = [
        'attachment_url',
    ];

    public function getAttachmentUrlAttribute(): string
    {
        if ($this->attachment && file_exists(public_path('assets/images/' . $this->attachment))) {
            return asset('assets/images/' . $this->attachment);
        }
        return asset('assets/images/system-config/default-picture.png');
    }

    /**
     * Get the purchase details associated with the purchase.
     */
    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class, 'purchase_id', 'id');
    }

    /**
     * Get the supplier associated with the purchase.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    /**
     * Get the payment method associated with the purchase.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }
}
