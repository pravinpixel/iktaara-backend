<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class OrderReportExport implements FromView
{
    public function view(): View
    {
        $filter_search_data = request()->filter_search_data;
        $date_range = request()->date_range;
        $filter_product_status = request()->filter_product_status;
        $start_date = $end_date = '';
        if (isset($date_range) && !empty($date_range)) {

            $dates = explode('-', $date_range);
            $start_date = date('Y-m-d', strtotime(trim(str_replace('/', '-', $dates[0]))));
            $end_date = date('Y-m-d', strtotime(trim(str_replace('/', '-', $dates[1]))));
        }
        $list = Order::selectRaw('mm_orders.*,mm_order_products.sub_total as order_value, mm_order_products.quantity as order_quantity, mm_order_products.status as order_status, mm_order_products.assigned_seller_1, mm_order_products.assigned_seller_2')
        ->join('order_products', 'order_products.order_id', '=', 'orders.id')
        ->join('merchant_orders', 'merchant_orders.order_id', '=', 'orders.id')
            ->when($start_date != '', function ($query) use ($start_date, $end_date) {
                $query->where(function ($q) use ($start_date, $end_date) {
                    $q->whereDate('orders.created_at', '>=', $start_date);
                    $q->whereDate('orders.created_at', '<=', $end_date);
                });
            })
            ->when($filter_search_data != '', function ($q) use ($filter_search_data) {
                $q->where('orders.order_no', $filter_search_data);
            })
            ->when($filter_product_status != '', function ($q) use ($filter_product_status) {
                $q->where('order_products.status', $filter_product_status);
            })
            ->groupBy('order_products.id')->orderBy('order_products.id', 'desc')
            ->get();

        return view('platform.order.reports._order_report_excel', compact('list'));
    }
}
