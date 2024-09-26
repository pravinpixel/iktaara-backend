<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Product\Product;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class InventoryReportExport implements FromView
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
        $list = Product::select('product_name', 'product_categories.name as secondary_category', 'product_categories.parent_id as primary_category', 'brands.brand_name', 'quantity', 'mrp', 'stock_status')
        ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
        ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->when($start_date != '', function ($query) use ($start_date, $end_date) {
                $query->where(function ($q) use ($start_date, $end_date) {
                    $q->whereDate('products.created_at', '>=', $start_date);
                    $q->whereDate('products.created_at', '<=', $end_date);
                });
            })
            ->when($filter_search_data != '', function ($q) use ($filter_search_data) {
                $q->where('products.product_name', $filter_search_data);
            })
            ->when($filter_product_status != '', function ($q) use ($filter_product_status) {
                $q->where('products.stock_status', $filter_product_status);
            })
            ->orderBy('products.id', 'desc')
            ->get();

        return view('platform.order.reports._inventory_report_excel', compact('list'));
    }
}
