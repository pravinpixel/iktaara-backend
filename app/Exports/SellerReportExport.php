<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Seller\Merchant;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class SellerReportExport implements FromView
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
        $list = Merchant::select(
            'pincodes.pincode',
            'states.state_name',
            'merchants.id',
            DB::raw("CONCAT(COALESCE(first_name,''),
            COALESCE(last_name,'')) as name"),
            'email',
            'mobile_no',
            'city',
            'merchant_shops_data.contact_person',
            'merchants.status',
            DB::raw("count(mm_merchant_orders.merchant_id) as order_on_hand")
        )
        ->leftjoin('merchant_orders', function($q)
        {
            $q->on('merchant_orders.merchant_id', '=', 'merchants.id')
            ->whereIn('merchant_orders.order_status', ['pending', 'accept', 'ship']);
        })
        ->join('states', 'merchants.state_id', '=', 'states.id')
        ->join('pincodes', 'merchants.pincode_id', '=', 'pincodes.id')
        ->leftjoin('merchant_shops_data', 'merchant_shops_data.merchant_id', '=', 'merchants.id')
        // ->havingRaw("COUNT(mm_merchant_orders.merchant_id) > 1")

            ->when($start_date != '', function ($query) use ($start_date, $end_date) {
                $query->where(function ($q) use ($start_date, $end_date) {
                    $q->whereDate('orders.created_at', '>=', $start_date);
                    $q->whereDate('orders.created_at', '<=', $end_date);
                });
            })
            ->when($filter_search_data != '', function ($q) use ($filter_search_data) {
                $q->where('merchants.first_name', $filter_search_data);
            })
            ->when($filter_product_status != '', function ($q) use ($filter_product_status) {
                $q->where('merchants.status', $filter_product_status);
            })
            ->groupBy('merchants.id')
            ->orderBy('merchants.id', 'desc')
            ->get();

        return view('platform.order.reports._seller_report_excel', compact('list'));
    }
}
