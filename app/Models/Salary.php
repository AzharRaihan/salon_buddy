<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    public function salaryDetails()
    {
        return $this->hasMany(SalaryDetail::class, 'salary_id', 'id');
    }

    public function salaryPayments(){
        return $this->hasMany(SalaryPayment::class, 'salary_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    
    
}
