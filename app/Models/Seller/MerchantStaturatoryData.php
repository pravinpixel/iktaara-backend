<?php

namespace App\Models\Seller;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantStaturatoryData extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'gst_no',
        'gst_document',
        'pan_no',
        'pan_document',
        'agree_document'
    ];
}
