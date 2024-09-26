<?php

namespace App\Models;

use App\Models\Master\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantOrder extends Model
{
    use HasFactory;

       protected $fillable = [
        'merchant_id',
        'order_id',
        'order_product_id',
        'order_status',
        'order_status_reason',
        'merchant_profit_margin',
        'seller_price',
        'total',
        'qty'
    ];

    public function getOrders(){
        return $this->hasMany(Order::class, 'id', 'order_id');
    }

    public function getMerchant(){
        return $this->hasOne(Merchant::class,'id', 'merchant_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderProduct::class, 'order_id', 'id');
    }

    public function payments()
    {
        return $this->hasOne(Payment::class,'order_id', 'id');
    }

    public function customer()
    {
        return $this->hasOne(Customer::class,'id', 'customer_id');
    }

    public function tracking()
    {
        return $this->hasMany(OrderHistory::class, 'order_id', 'id');
    }

    public function getInvoiceFileAttribute(){
        return asset('storage/invoice_order/'.$this->attributes['order_no'].'.pdf');
    }

    public function getOrderDateAttribute(){
        return date( 'd M Y H:i A', strtotime( $this->attributes['created_at'] ));
    }

    public function getStatusAttribute(){
        if($this->attributes['status'] == 'placed'){
            return 'Order Placed';
        }elseif($this->attributes['status'] == 'shipped'){
            return 'Order Shipped';
        }elseif($this->attributes['status'] == 'delivered'){
            return 'Order Delivered';
        }elseif($this->attributes['status'] == 'cancelled'){
            return 'Order Cancelled';
        }elseif($this->attributes['status'] == 'cancel_requested'){
            return 'Cancel Requested';
        }elseif($this->attributes['status'] == 'payment_pending'){
            return 'Payment Pending';
        }else{
            return $this->attributes['status'];
        }
    }



}
