<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Illuminate\Support\Facades\Storage;


class OrderStatus extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'status_name',
        'description',
        'order',
        'added_by',
        'status',
        'show_in_front',
        'image'
    ];
    public function added()
    {
        return $this->hasOne(User::class, 'id', 'added_by');
    }

    public function getImageAttribute(){
        if(isset($this->attributes['image'])){

                return asset(Storage::url('order_status/'.$this->attributes['image']));

        }else{
            return '';
        }


    }
}
