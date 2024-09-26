<?php

namespace App\Http\Controllers;

use App\Models\Reviews;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReivewsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data               = Reviews::select('reviews.*', 'reviews.star as rating', 'orders.billing_name as customer_name', 'orders.order_no', 'orders.billing_mobile_no as customer_mobile', 'products.product_name as product_name')
                ->leftJoin('orders', 'orders.id', '=', 'reviews.order_id')
                ->leftJoin('products', 'products.id', '=', 'reviews.product_id')
                ->orderBy('reviews.id', 'DESC');

            $status             = $request->get('status');
            $keywords           = $request->get('search')['value'];
            $datatables         =  Datatables::of($data)
                ->filter(function ($query) use ($keywords, $status) {
                    if ($status) {
                        return $query->where('reviews.status', '=', "$status");
                    }
                    if ($keywords) {
                        $date = date('Y-m-d', strtotime($keywords));
                        return $query->where('products.product_name', 'like', "%{$keywords}%")->orWhere('orders.billing_name', 'like', "%{$keywords}%")->orWhere('reviews.comments', 'like', "%{$keywords}%")->orWhereDate("testimonials.created_at", $date);
                    }
                })
                ->addIndexColumn()

                ->editColumn('order_no', function ($row) {
                    return ucwords($row->order_no);
                })

                ->editColumn('product_name', function ($row) {
                    return ucwords($row->product_name);
                })

                ->editColumn('customer_name', function ($row) {
                    $billing_info = '';
                    $billing_info .= '<div class="font-weight-bold">' . $row['customer_name'] . '</div>';
                    $billing_info .= '<div class="">' . $row['customer_mobile'] . '</div>';
                    return $billing_info;
                })

                ->editColumn('rating', function ($row) {
                    return ucwords($row->rating);
                })

                ->editColumn('status', function ($row) {
                    $status = '<a href="javascript:void(0);" class="badge badge-light-' . (($row->status == 1) ? 'success' : 'danger') . '" tooltip="Click to ' . (($row->status == 0) ? 'Unpublish' : 'Publish') . '" onclick="return commonChangeStatus(' . $row->id . ', \'' . (($row->status == 1) ? 'unpublished' : 'published') . '\', \'product-review\')">' . (($row->status == 0) ? 'unpublished' : 'published')  . '</a>';
                    return $status;
                })


                ->editColumn('created_at', function ($row) {
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $row['created_at'])->format('d-m-Y');
                    return $created_at;
                })

                ->addColumn('action', function ($row) {

                    $view_btn = '<a href="javascript:void(0)" onclick="return viewReview(' . $row->id . ')" class="btn btn-icon btn-active-primary btn-light-primary mx-1 w-30px h-30px" >
                    <i class="fa fa-eye"></i>
                </a>';

                    $view_btn  .= '<a href="javascript:void(0);" onclick="return commonDelete(' . $row->id . ', \'product-review\')" class="btn btn-icon btn-active-danger btn-light-danger mx-1 w-30px h-30px" >
                <i class="fa fa-trash"></i></a>';

                    return  $view_btn;
                })
                ->rawColumns(['action', 'status', 'product_name', 'order_no', 'customer_name']);
            return $datatables->make(true);
        }
        $breadCrum  = array('Products', 'Reviews');
        $title      = 'Product Reviews';
        return view('platform.reviews.index', compact('breadCrum', 'title'));
    }

    public function view(Request $request)
    {
        $id         = $request->id;
        $modal_title        = 'Review View';
        $review       = Reviews::select('reviews.*', 'products.product_name', 'orders.order_no', 'customers.email', 'customers.mobile_no')
            ->leftJoin('products', 'products.id', '=', 'reviews.product_id')
            ->leftJoin('orders', 'orders.id', '=', 'reviews.order_id')
            ->leftJoin('customers', 'customers.id', '=', 'reviews.customer_id')
            ->where('reviews.id', $id)
            ->first();

        return view('platform.reviews.view_modal', compact('review', 'modal_title'));
    }

    public function delete(Request $request, Reviews $reviews)
    {
        $id         = $request->id;
        $review       = Reviews::find($id);
        $review->delete();
        return response()->json(['message' => "Successfully deleted!", 'status' => 1]);
    }

    public function changeStatus(Request $request, Reviews $reviews)
    {
        $id             = $request->id;
        $status         = $request->status;
        $reviews           = Reviews::find($id);
        $reviews->status   = ($status == 'published') ? 1 : 0;
        $reviews->update();
        return response()->json(['statusCode' => '200', 'message' => "Your changes are done", 'status' => 1]);
    }
}
