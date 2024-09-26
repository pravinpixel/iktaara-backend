<?php

namespace App\Models;

use App\Models\Master\OrderStatus;
use App\Models\Product\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OrderProduct extends Model
{
    use HasFactory;

    protected $appends = [
        'product_image'
    ];

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'hsn_code',
        'sku',
        'quantity',
        'price',
        'mrp',
        'tax_amount',
        'tax_percentage',
        'sub_total',
        'assigned_to_merchant',
        'assigned_seller_1',
        'assigned_seller_2',
        'status',
        'shipment_tracking_code',
        'shipment_tracking_message'
    ];

    public function products()
    {
        return $this->hasOne(Product::class, 'id', 'product_id')->withTrashed();
    }

    public function getStatus()
    {
        return $this->hasOne(OrderStatus::class, 'id', 'status')->withTrashed();
    }

    public function tracking()
    {
        return $this->hasManyThrough(OrderHistory::class, ['product_id', 'product_id'], OrderHistory::class, ['order_id', 'order_id'])->orderBy('order_histories.id', 'desc');
    }

    public function getTracking($order_id, $product_id){
        
        $order_history = OrderHistory::where([['order_id', $order_id], ['product_id', $product_id]])
                                        ->select('action', 'created_at')
                                        ->get();
        return $order_history;
    }


    public function getProductImageAttribute()
    {
        $product = \App\Models\Product\Product::find($this->product_id);
        if (isset($product->base_image)) {
            if (!Storage::exists($product->base_image)) {
                return asset('assets/logo/no-img-1.png');
            } else {
                return asset(Storage::url($product->base_image));
            }
        } else {
            return '';
        }
    }
}
