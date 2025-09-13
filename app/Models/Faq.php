<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',
        'company_id',
        'del_status'
    ];
}
