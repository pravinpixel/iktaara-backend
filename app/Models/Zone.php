<?php

namespace App\Models;

use App\Models\Master\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;


    protected $fillable = [
        'zone_name',
        'zone_order',
        'status'
    ];
    public function collectionStates()
    {
        return $this->hasMany(ZoneState::class, 'zone_id', 'id' );
    }
}
