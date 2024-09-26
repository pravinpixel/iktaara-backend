<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerRequestDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'email',
        'mobile_no',
        'location',
        'pincode',
        'customer_categories',
        'customer_designation',
        'is_agree',
        'desc'
    ];
}