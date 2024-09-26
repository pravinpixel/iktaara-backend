<?php

namespace App\Http\Controllers;

use App\Exports\InventoryReportExport;
use App\Exports\OrderReportExport;
use App\Exports\ReportExport;
use App\Exports\SellerReportExport;
use App\Models\Category\MainCategory;
use App\Models\Master\Brands;
use App\Models\Master\Customer;
use App\Models\Master\OrderStatus;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Payment;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Seller\Merchant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Calculation\Category;

class ReportProductController extends Controller
{
    public function index(Request $request)
    {
        $title                  = "Sales Report";
        $breadCrum              = array('Reports', 'Sales');
        if ($request->ajax()) {

            $data = Order::selectRaw('mm_orders.*,sum(mm_order_products.quantity) as order_quantity,sum(mm_order_products.sub_total) as order_amount, mm_order_products.assigned_seller_1, mm_order_products.assigned_seller_2')
                ->join('order_products', 'order_products.order_id', '=', 'orders.id')
                ->whereNotIn('order_products.status', [1, 3, 6, 7, 9, 14])
                ->where('order_products.assigned_to_merchant', 'assigned')
                ->groupBy('order_products.id');

            $keywords = $request->get('search')['value'];
            $filter_search_data = $request->get('filter_search_data');
            $date_range = $request->get('date_range');
            $filter_product_status = $request->get('filter_product_status');
            $start_date = $end_date = '';
            if (isset($date_range) && !empty($date_range)) {

                $dates = explode('-', $date_range);
                $start_date = date('Y-m-d', strtotime(trim(str_replace('/', '-', $dates[0]))));
                $end_date = date('Y-m-d', strtotime(trim(str_replace('/', '-', $dates[1]))));
            }

            $datatables =  DataTables::of($data)
                ->filter(function ($query) use ($keywords, $start_date, $end_date, $filter_search_data, $filter_product_status) {

                    if ($filter_product_status) {
                        $query->where('orders.status', $filter_product_status);
                    }
                    if ($filter_search_data) {
                        $query->where('orders.order_no', 'like', "%{$filter_search_data}%");
                    }
                    if (!empty($start_date) && !empty($end_date)) {
                        $query->where(function ($q) use ($start_date, $end_date) {
                            $q->whereDate('orders.created_at', '>=', $start_date);
                            $q->whereDate('orders.created_at', '<=', $end_date);
                        });
                    }
                    if ($keywords) {
                        $date = date('Y-m-d', strtotime($keywords));
                        $query->where('orders.billing_name', 'like', "%{$keywords}%")
                            ->orWhere('orders.order_no', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_email', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_mobile_no', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_address_line1', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_state', 'like', "%{$keywords}%")
                            ->orWhereDate("orders.created_at", $date);
                    }
                })
                ->addIndexColumn()
                ->editColumn('seller_name', function ($row) {
                    if (isset($row->assigned_seller_2)) {
                        $seller_name = (Merchant::getMerchantName($row->assigned_seller_2)) ? Merchant::getMerchantName($row->assigned_seller_2) : 'Not Assigned';
                    } elseif (isset($row->assigned_seller_1)) {
                        $seller_name = Merchant::getMerchantName($row->assigned_seller_1) ? Merchant::getMerchantName($row->assigned_seller_1) : 'Not Assigned';
                    } else {
                        $seller_name = "Not assigned";
                    }

                    return $seller_name;
                })

                ->editColumn('location', function ($row) {
                    $seller_location = '';
                    if ($row->assigned_seller_2) {
                        $seller_location = Merchant::getMerchantLocation($row->assigned_seller_2);
                    } elseif ($row->assigned_seller_1) {
                        $seller_location = Merchant::getMerchantLocation($row->assigned_seller_1);
                    }
                    return $seller_location;
                })
                ->editColumn('customer_zone', function ($row) {
                    return findZoneByStateName($row->billing_state);
                })
                // ->editColumn('created_at', function ($row) {
                //     $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row['created_at'])->format('d-m-Y');
                //     return $created_at;
                // })


                ->rawColumns(['seller_name', 'location', 'customer_zone']);
            return $datatables->make(true);
        }

        $addHref = route('products.add.edit');
        $routeValue = 'products';
        $productCategory        = ProductCategory::where('status', 'published')->get();
        $brands                 = Brands::where('status', 'published')->get();
        $productLabels          = MainCategory::where(['slug' => 'product-labels', 'status' => 'published'])->first();
        $productTags            = MainCategory::where(['slug' => 'product-tags', 'status' => 'published'])->first();

        $params                 = array(
            'title' => $title,
            'breadCrum' => $breadCrum,
            'addHref' => $addHref,
            'routeValue' => $routeValue,
            'productCategory' => $productCategory,
            'brands' => $brands,
            'productLabels' => $productLabels,
            'productTags' => $productTags,
        );

        return view('platform.reports.products.list', $params);
    }


    public function ordersReport(Request $request)
    {
        $title                  = "Orders Report";
        $breadCrum              = array('Reports', 'Orders');
        if ($request->ajax()) {

            $data = Order::selectRaw('mm_orders.*,mm_order_products.sub_total as order_value, mm_order_products.quantity as order_quantity, mm_order_products.status as order_status, mm_order_products.assigned_seller_1, mm_order_products.assigned_seller_2')
                ->join('order_products', 'order_products.order_id', '=', 'orders.id')
                ->join('merchant_orders', 'merchant_orders.order_id', '=', 'orders.id')
                ->groupBy('order_products.id')
                ->orderBy('order_products.id', 'desc');

            $keywords = $request->get('search')['value'];
            $filter_search_data = $request->get('filter_search_data');
            $date_range = $request->get('date_range');
            $filter_product_status = $request->get('filter_product_status');
            $start_date = $end_date = '';
            if (isset($date_range) && !empty($date_range)) {

                $dates = explode('-', $date_range);
                $start_date = date('Y-m-d', strtotime(trim(str_replace('/', '-', $dates[0]))));
                $end_date = date('Y-m-d', strtotime(trim(str_replace('/', '-', $dates[1]))));
            }

            $datatables =  DataTables::of($data)
                ->filter(function ($query) use ($keywords, $start_date, $end_date, $filter_search_data, $filter_product_status) {

                    if ($filter_product_status) {
                        $query->where('order_products.status', $filter_product_status);
                    }
                    if ($filter_search_data) {
                        $query->where('orders.order_no', 'like', "%{$filter_search_data}%");
                    }
                    if (!empty($start_date) && !empty($end_date)) {
                        $query->where(function ($q) use ($start_date, $end_date) {
                            $q->whereDate('orders.created_at', '>=', $start_date);
                            $q->whereDate('orders.created_at', '<=', $end_date);
                        });
                    }
                    if ($keywords) {
                        $date = date('Y-m-d', strtotime($keywords));
                        $query->where('orders.billing_name', 'like', "%{$keywords}%")
                            ->orWhere('orders.order_no', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_email', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_mobile_no', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_address_line1', 'like', "%{$keywords}%")
                            ->orWhere('orders.billing_state', 'like', "%{$keywords}%")
                            ->orWhereDate("orders.created_at", $date);
                    }
                })
                ->addIndexColumn()

                ->editColumn('order_status', function ($row) {
                    $order_status = OrderStatus::where('id', $row->order_status)->select('status_name')->pluck('status_name')->first();
                    return ucwords($order_status);
                })
                ->editColumn('seller_name', function ($row) {
                    $seller_name = "Not assigned";
                    if (isset($row->assigned_seller_2)) {
                        $seller_name = Merchant::getMerchantName($row->assigned_seller_2) ? Merchant::getMerchantName($row->assigned_seller_2) : 'Not Assigned';
                    } elseif (isset($row->assigned_seller_1)) {
                        $seller_name = Merchant::getMerchantName($row->assigned_seller_1) ? Merchant::getMerchantName($row->assigned_seller_1) : 'Not Assigned';
                    } elseif (!$row->assigned_seller_2 && !$row->assigned_seller_1) {
                        $seller_name = "Not assigned";
                    } else {
                        $seller_name = "Not assigned";
                    }

                    return $seller_name;
                })
                ->editColumn('created_at', function ($row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row['created_at'])->format('d-m-Y H:i');
                    return $created_at;
                })


                ->rawColumns(['seller_name', 'order_status', 'created_at']);
            return $datatables->make(true);
        }

        $addHref = route('products.add.edit');
        $routeValue = 'products';
        $orderStatus = OrderStatus::where('show_in_front', 1)->orderBy('order', 'asc')->get();
        $productCategory        = ProductCategory::where('status', 'published')->get();
        $brands                 = Brands::where('status', 'published')->get();
        $productLabels          = MainCategory::where(['slug' => 'product-labels', 'status' => 'published'])->first();
        $productTags            = MainCategory::where(['slug' => 'product-tags', 'status' => 'published'])->first();

        $params                 = array(
            'title' => $title,
            'breadCrum' => $breadCrum,
            'addHref' => $addHref,
            'routeValue' => $routeValue,
            'productCategory' => $productCategory,
            'brands' => $brands,
            'productLabels' => $productLabels,
            'productTags' => $productTags,
            'orderStatus' => $orderStatus
        );

        return view('platform.reports.products.orders', $params);
    }

    public function sellerReport(Request $request)
    {
        $title                  = "Seller Report";
        $breadCrum              = array('Reports', 'Seller');
        if ($request->ajax()) {

            $data = Merchant::select(
                'pincodes.pincode',
                'states.state_name',
                'merchants.id',
                DB::raw("CONCAT(COALESCE(first_name,''),' ',
            COALESCE(last_name,'')) as name"),
                'email',
                'mobile_no',
                'city',
                'merchant_shops_data.contact_person',
                'merchants.status',
                DB::raw("count(mm_merchant_orders.merchant_id) as order_on_hand")
            )

                ->leftjoin('merchant_orders', function ($q) {
                    $q->on('merchant_orders.merchant_id', '=', 'merchants.id')
                        ->whereIn('merchant_orders.order_status', ['pending', 'accept', 'ship']);
                })
                ->join('states', 'merchants.state_id', '=', 'states.id')
                ->join('pincodes', 'merchants.pincode_id', '=', 'pincodes.id')
                ->leftjoin('merchant_shops_data', 'merchant_shops_data.merchant_id', '=', 'merchants.id')
                // ->havingRaw("COUNT(mm_merchant_orders.merchant_id) > 1")
                ->groupBy('merchants.id')
                ->orderBy('merchants.id', 'desc');

            $keywords = $request->get('search')['value'];
            $filter_search_data = $request->get('filter_search_data');
            $date_range = $request->get('date_range');
            $filter_product_status = $request->get('filter_product_status');
            $start_date = $end_date = '';
            if (isset($date_range) && !empty($date_range)) {

                $dates = explode('-', $date_range);
                $start_date = date('Y-m-d', strtotime(trim(str_replace('/', '-', $dates[0]))));
                $end_date = date('Y-m-d', strtotime(trim(str_replace('/', '-', $dates[1]))));
            }

            $datatables =  DataTables::of($data)
                ->filter(function ($query) use ($keywords, $start_date, $end_date, $filter_search_data, $filter_product_status) {

                    if ($filter_product_status) {
                        $query->where('merchants.status', $filter_product_status);
                    }
                    if ($filter_search_data) {
                        $query->where(DB::raw("CONCAT(mm_merchants.first_name,' ', mm_merchants.last_name)"), 'like', "%{$filter_search_data}%");
                    }
                    if (!empty($start_date) && !empty($end_date)) {
                        $query->where(function ($q) use ($start_date, $end_date) {
                            $q->whereDate('orders.created_at', '>=', $start_date);
                            $q->whereDate('orders.created_at', '<=', $end_date);
                        });
                    }
                    if ($keywords) {
                        $date = date('Y-m-d', strtotime($keywords));
                        $query->where('merchants.first_name', 'like', "%{$keywords}%")
                            ->orWhere('merchants.last_name', 'like', "%{$keywords}%")
                            ->orWhere('merchant_shops_data.contact_person', 'like', "%{$keywords}%")
                            ->orWhere('states.state_name', 'like', "%{$keywords}%")
                            ->orWhere('merchants.city', 'like', "%{$keywords}%")
                            ->orWhere('pincodes.pincode', 'like', "%{$keywords}%");
                    }
                })
                ->addIndexColumn()

                ->editColumn('status', function ($row) {
                    return ucwords($row->status);
                })



                ->rawColumns(['status']);
            return $datatables->make(true);
        }

        $addHref = route('products.add.edit');
        $routeValue = 'products';
        $productCategory        = ProductCategory::where('status', 'published')->get();
        $brands                 = Brands::where('status', 'published')->get();
        $productLabels          = MainCategory::where(['slug' => 'product-labels', 'status' => 'published'])->first();
        $productTags            = MainCategory::where(['slug' => 'product-tags', 'status' => 'published'])->first();

        $params                 = array(
            'title' => $title,
            'breadCrum' => $breadCrum,
            'addHref' => $addHref,
            'routeValue' => $routeValue,
            'productCategory' => $productCategory,
            'brands' => $brands,
            'productLabels' => $productLabels,
            'productTags' => $productTags,
        );

        return view('platform.reports.products.seller', $params);
    }
    public function inventoryReport(Request $request)
    {
        $title                  = "Inventory Report";
        $breadCrum              = array('Reports', 'Inventory');
        if ($request->ajax()) {

            $data = Product::select('product_name', 'product_categories.name as secondary_category', 'product_categories.parent_id as primary_category', 'brands.brand_name', 'quantity', 'mrp', 'stock_status')
                ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
                ->join('brands', 'products.brand_id', '=', 'brands.id')
                ->orderBy('products.id', 'desc');

            $keywords = $request->get('search')['value'];
            $filter_search_data = $request->get('filter_search_data');
            $date_range = $request->get('date_range');
            $filter_product_status = $request->get('filter_product_status');
            $start_date = $end_date = '';
            if (isset($date_range) && !empty($date_range)) {

                $dates = explode('-', $date_range);
                $start_date = date('Y-m-d', strtotime(trim(str_replace('/', '-', $dates[0]))));
                $end_date = date('Y-m-d', strtotime(trim(str_replace('/', '-', $dates[1]))));
            }

            $datatables =  DataTables::of($data)
                ->filter(function ($query) use ($keywords, $start_date, $end_date, $filter_search_data, $filter_product_status) {

                    if ($filter_product_status) {
                        $query->where('products.stock_status', $filter_product_status);
                    }
                    if ($filter_search_data) {
                        $query->where('products.product_name', 'like', "%{$filter_search_data}%");
                    }
                    if (!empty($start_date) && !empty($end_date)) {
                        $query->where(function ($q) use ($start_date, $end_date) {
                            $q->whereDate('products.created_at', '>=', $start_date);
                            $q->whereDate('products.created_at', '<=', $end_date);
                        });
                    }
                    if ($keywords) {
                        $query->where('products.product_name', 'like', "%{$keywords}%")
                            ->orWhere('product_categories.name', 'like', "%{$keywords}%")
                            ->orWhere('products.stock_status', 'like', "%{$keywords}%")
                            ->orWhere('brands.brand_name', 'like', "%{$keywords}%");
                    }
                })
                ->addIndexColumn()

                ->editColumn('stock_status', function ($row) {
                    return ucwords(str_replace('_', ' ', $row->stock_status));
                })
                ->editColumn('primary_category', function ($row) {
                    $category = ProductCategory::find($row->primary_category);
                    return ($category) ? $category->name : '';
                })


                ->rawColumns(['stock_status']);
            return $datatables->make(true);
        }

        $addHref = route('products.add.edit');
        $routeValue = 'products';
        $productCategory        = ProductCategory::where('status', 'published')->get();
        $brands                 = Brands::where('status', 'published')->get();
        $productLabels          = MainCategory::where(['slug' => 'product-labels', 'status' => 'published'])->first();
        $productTags            = MainCategory::where(['slug' => 'product-tags', 'status' => 'published'])->first();

        $params                 = array(
            'title' => $title,
            'breadCrum' => $breadCrum,
            'addHref' => $addHref,
            'routeValue' => $routeValue,
            'productCategory' => $productCategory,
            'brands' => $brands,
            'productLabels' => $productLabels,
            'productTags' => $productTags,
        );

        return view('platform.reports.products.inventory', $params);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new ReportExport, 'sales_report.xlsx');
    }

    public function orderExportExcel(Request $request)
    {
        return Excel::download(new OrderReportExport, 'order_report.xlsx');
    }

    public function sellerExportExcel(Request $request)
    {
        return Excel::download(new SellerReportExport, 'seller_report.xlsx');
    }

    public function inventoryExportExcel(Request $request)
    {
        return Excel::download(new InventoryReportExport, 'inventory_report.xlsx');
    }

    public function adminDashboardReports(Request $request)
    {

            //inventory
            $total_stock_count = Product::where([['status', '=', 'published'], ['stock_status', 'in_stock']])->count();
            $out_of_stock_items_count = Product::where([['status', '=', 'published'], ['stock_status', 'out_of_stock']])->count();
            $low_stock_items_count = Product::where([['status', '=', 'published'], ['quantity', '<=',1]])->count();

            //vendors
            $newly_added_merchants_count = Merchant::where([['status', '=', 'registered']])->count();
            $total_merchants_count = Merchant::get()->count();
            $active_merchants = Merchant::where([['mode', 'active']])->count();
            $in_active_merchants = Merchant::where([['mode', 'in_active']])->count();
            $total_products = Merchant::join('merchant_products', 'merchant_products.merchant_id', 'merchants.id')->groupBy('merchant_products.product_id')->count();
            $top_selling_products = OrderProduct::select( DB::raw("count(mm_products.id) as total_count, sum(mm_order_products.sub_total) as total_amount"), 'products.*' )
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->join('products', 'products.id', '=', 'order_products.product_id')
            ->where('orders.status', '!=', 'pending')
            ->groupBy('products.id')
            ->limit(10)
            ->orderBy('total_amount', 'desc')
            ->get();

            //Orders
            $pending_orders_sub_query = OrderProduct::select('order_id')->join('orders', 'order_products.order_id', 'orders.id')->whereIn('order_products.status', [1,2])->groupBy('order_id');

            $pending_orders = DB::table( DB::raw("({$pending_orders_sub_query->toSql()}) as sub") )
                ->mergeBindings($pending_orders_sub_query->getQuery())
                ->count();

            $confirmed_orders_sub_query = OrderProduct::select('order_id')->join('orders', 'order_products.order_id', 'orders.id')->where([['order_products.status', 8]])->groupBy('order_id');
            $confirmed_orders = DB::table( DB::raw("({$confirmed_orders_sub_query->toSql()}) as sub") )
            ->mergeBindings($confirmed_orders_sub_query->getQuery())
            ->count();
            $shipped_orders_sub_query = OrderProduct::select('order_id')->join('orders', 'order_products.order_id', 'orders.id')->where([['order_products.status', 4]])->groupBy('order_id');
            $shipped_orders = DB::table( DB::raw("({$shipped_orders_sub_query->toSql()}) as sub") )
            ->mergeBindings($shipped_orders_sub_query->getQuery())
            ->count();
            $delivered_orders_sub_query = OrderProduct::select('order_id')->join('orders', 'order_products.order_id', 'orders.id')->where([['order_products.status', 5]])->groupBy('order_id');
            $delivered_orders = DB::table( DB::raw("({$delivered_orders_sub_query->toSql()}) as sub") )
            ->mergeBindings($delivered_orders_sub_query->getQuery())
            ->count();
            $exchanged_orders_sub_query = OrderProduct::select('order_id')->join('orders', 'order_products.order_id', 'orders.id')->whereIn('order_products.status', [10,11,12,13])->groupBy('order_id');
            $exchanged_orders = DB::table( DB::raw("({$exchanged_orders_sub_query->toSql()}) as sub") )
            ->mergeBindings($exchanged_orders_sub_query->getQuery())
            ->count();
            $cancelled_orders_sub_query = OrderProduct::select('order_id')->join('orders', 'order_products.order_id', 'orders.id')->whereIn('order_products.status', [3,6])->groupBy('order_id');
            $cancelled_orders = DB::table( DB::raw("({$cancelled_orders_sub_query->toSql()}) as sub") )
            ->mergeBindings($cancelled_orders_sub_query->getQuery())
            ->count();
            $prepaid_orders_sub_query = Order::select('order_id')->join('payments', 'payments.order_id', 'orders.id')->groupBy('order_id');
            $prepaid_orders = DB::table( DB::raw("({$prepaid_orders_sub_query->toSql()}) as sub") )
            ->mergeBindings($prepaid_orders_sub_query->getQuery())
            ->count();

            //Sales

            $total_revenue = Payment::where('status', 'paid')->sum('amount');
            // $total_orders = Order::get()->count();

            $total_orders_sub_query = OrderProduct::select('order_id')->join('orders', 'order_products.order_id', 'orders.id')->groupBy('order_id');

            $total_orders = DB::table( DB::raw("({$total_orders_sub_query->toSql()}) as sub") )
                ->mergeBindings($total_orders_sub_query->getQuery())
                ->count();


            $average_revenue = $total_revenue / $total_orders;
            $total_quantity = OrderProduct::sum('quantity');
            $total_customers = Customer::where('status', 'published')->count();
            $sales_by_category = OrderProduct::select( DB::raw("count(mm_product_categories.id) as total_count, sum(mm_order_products.sub_total) as total_amount"), 'product_categories.*' )
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->join('products', 'products.id', '=', 'order_products.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->where('orders.status', '!=', 'pending')
            ->groupBy('product_categories.id')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();
            $sales_by_zone = OrderProduct::select( DB::raw("count(mm_zone_states.zone_id) as total_count, sum(mm_order_products.sub_total) as total_amount"), 'zones.*' )
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->join('states', 'states.state_name', '=', 'orders.billing_state')
            ->join('zone_states', 'zone_states.state_id', '=', 'states.id')
            ->join('zones', 'zones.id', '=', 'zone_states.zone_id')
            ->where('orders.status', '!=', 'pending')
            ->groupBy('zone_states.zone_id')
            ->orderBy('total_amount', 'desc')
            ->get();
            $abandoned_cart = DB::select("SELECT count(single_cart_count) as abandoned_cart_count, sum(single_cart_total) as abandoned_cart_total from (SELECT COUNT(*) as single_cart_count, SUM(sub_total) as single_cart_total FROM `mm_carts`
            group by token) new_table");


            $report_values = [
                'total_stock_count' => $total_stock_count,
                'out_of_stock_items_count' => $out_of_stock_items_count,
                'low_stock_items_count' => $low_stock_items_count,
                'newly_added_merchants_count' => $newly_added_merchants_count,
                'total_merchants_count' =>$total_merchants_count,
                'active_merchants' => $active_merchants,
                'in_active_merchants' => $in_active_merchants,
                'total_products' => $total_products,
                'top_selling_products' => $top_selling_products,
                'pending_orders' => $pending_orders,
                'confirmed_orders' => $confirmed_orders,
                'shipped_orders' => $shipped_orders,
                'delivered_orders' => $delivered_orders,
                'exchanged_orders' =>$exchanged_orders,
                'cancelled_orders' => $cancelled_orders,
                'prepaid_orders' => $prepaid_orders,
                'total_revenue' => $total_revenue,
                'total_orders' => $total_orders,
                'average_revenue' => $average_revenue,
                'total_quantity' => $total_quantity,
                'total_customers' => $total_customers,
                'sales_by_category' => $sales_by_category,
                'sales_by_zone' => $sales_by_zone,
                'abandoned_cart' => $abandoned_cart[0]
            ];



        return view('platform.reports.admin_dashboard.reports', $report_values);
    }
}
