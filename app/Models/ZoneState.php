<?php

namespace App\Models;

use App\Models\Master\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneState extends Model
{
    use HasFactory;


    protected $fillable = [
        'zone_id',
        'state_id'
    ];

}
