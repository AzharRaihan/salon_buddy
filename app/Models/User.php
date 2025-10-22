<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    protected $appends = [
        'role_name',
        'status_name',
        'photo_url',
        'role_s_name',
        'temp_keyword',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    protected $guard_name = 'sanctum';

    public function getRoleNameAttribute(): string
    {
        return $this->role == 1 ? 'Admin' : 'User';
    }
    public function getTempKeywordAttribute()
    {
        return $this->password;
    }

    public function getStatusNameAttribute(): string
    {
        return $this->status == 'Active' ? 'Active' : 'Inactive';
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && file_exists(public_path('assets/images/' . $this->photo))) {
            return asset('assets/images/' . $this->photo);
        }

        return asset('assets/images/default-images/avatar.png');
    }

    public function getRoleSNameAttribute(): string
    {
        return $this->getRoleNames();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function booking()
    {
        return $this->hasMany(BookingDetail::class, 'service_seller_id', 'id');
    }

    public function bookingDetails()
    {
        return $this->hasMany(BookingDetail::class, 'service_seller_id', 'id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'employee_id', 'id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id', 'id');
    }

    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class, 'employee_id', 'id');
    }

    public function staffPayments(): HasMany
    {
        return $this->hasMany(StaffPayment::class, 'employee_id', 'id');
    }


    // write a relationship to user to purchase
    public function purchase(): HasMany
    {
        return $this->hasMany(Purchase::class, 'user_id', 'id');
    }

    public function supplierPayment(): HasMany
    {
        return $this->hasMany(SupplierPayment::class, 'user_id', 'id');
    }

    public function staffPayment(): HasMany
    {
        return $this->hasMany(StaffPayment::class, 'user_id', 'id');
    }




}
