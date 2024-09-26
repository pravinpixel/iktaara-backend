<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class OrderExport implements FromView
{
    public function view(): View
    {
        $list = Order::selectRaw('mm_payments.order_id,
        mm_payments.payment_no,
        mm_payments.status as payment_status,
        mm_orders.*,
        sum(mm_order_products.quantity) as order_quantity,
        mm_order_products.shipment_tracking_code,
        mm_order_products.shipment_tracking_message,
        group_concat(mm_order_products.status) as order_status
        ')
            ->join('order_products', 'order_products.order_id', '=', 'orders.id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->groupBy('orders.id')->orderBy('orders.id', 'desc')->get();
        $list_data = [];
        foreach ($list as $data) {
            $data->is_coupon = ($data->is_coupon == 1) ? 'Yes' : 'No';
            $data->billing_info = $data->billing_name . ' ' . $data->billing_mobile_no;
            $data->payment_status = ucwords($data->payment_status);
            $data->created_at = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y h:i:sa');
            $list_data[] = $data;
        }

        return view('platform.order._excel', compact('list_data'));
    }
}
