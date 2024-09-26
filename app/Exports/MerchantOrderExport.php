<?php

namespace App\Exports;

use App\Models\MerchantOrder;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Seller\Merchant;
use App\Models\Order;
use App\Models\Seller\MerchantProfit;
use App\Models\Product\Product;
use Carbon\Carbon;

class MerchantOrderExport implements FromView
{
    public function view(): View
    {
        $data = Order::selectRaw('DISTINCT mm_order_products.id as order_product_id,
        mm_payments.order_id,
        mm_merchant_orders.id as merchant_order_id,
        mm_merchant_orders.order_status as merchant_order_status,
        mm_orders.*,
        mm_order_products.quantity as order_quantity,
        mm_merchants.merchant_no,
        mm_merchants.id as merchant_id,
        mm_merchant_orders.seller_price as total_amount,
        mm_merchant_orders.merchant_profit_margin,
        mm_merchant_orders.total as product_value,
        mm_order_products.product_id,
        mm_payments.status as payment_status,
        mm_order_products.assigned_to_merchant,
        mm_order_products.assigned_seller_1,
        mm_order_products.assigned_seller_2,
        mm_order_products.status as order_status',)
->leftJoin('merchant_orders', 'merchant_orders.order_id', '=', 'orders.id')
->Join('order_products', 'order_products.order_id', '=', 'orders.id')
->Join('merchants','merchants.id','=', 'merchant_orders.merchant_id')
->join('payments', 'payments.order_id', '=', 'orders.id')
->groupBy('merchant_orders.id','orders.id')
->orderBy('orders.id', 'desc')
->get();
    
        // Initialize an array to hold the modified data
        $modifiedData = [];
    
        foreach ($data as $item) {
            $product_id = $item->product_id;
            $merchant_id = $item->merchant_id;
            $mrp = $item->total_amount;
    
            // Retrieve product and related information
            $product = Product::find($product_id);
            $parent_category_id = $product->productCategory->parent->id ?? '';
            $brand_id = $product->productBrand->id ?? '';
    
            // Get profit margin based on brand or category
            $brand_profit_margin = MerchantProfit::where([['merchant_id', $merchant_id], ['brand_id', $brand_id]])->first();
            if (!$brand_profit_margin) {
                $category_profit_margin = MerchantProfit::where([['merchant_id', $merchant_id], ['category_id', $parent_category_id]])->first();
                $profit_margin = $category_profit_margin ? $category_profit_margin->category_margin_value : null;
            } else {
                $profit_margin = $brand_profit_margin->brand_margin_value;
            }
    
            // Calculate profit margin and add it to the modified data
            $profit_margin_value = round((($profit_margin / 100) * $mrp), 2);
            $item->profit_margin = number_format($mrp - $profit_margin_value, 2);
    
            // Push the modified item to the array
            $modifiedData[] = $item;
        }
   
        // Pass the modified data to the view
        return view('platform.exports.merchantorder.excel', compact('modifiedData'));
    }
    
}
