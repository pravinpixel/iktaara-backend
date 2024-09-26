<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProductRequest;
use App\Providers\RouteServiceProvider;
use DataTables;

class ProductRequestController extends Controller
{

    // use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function index(Request $request)
    {
        $title                  = "Product Request";
        $breadCrum              = array('Products', 'Product Request');

        if ($request->ajax()) {
            $data               = ProductRequest::orderBy('id', 'DESC')->get();//Zone::select('zone_id','zone_name','zone_order', 'status')->selectRaw('GROUP_CONCAT(state) as states')->groupBy('zone_id')->get();
            $keywords           = $request->get('search')['value'];
            $datatables         = Datatables::of($data)
            ->filter(function ($query) use ($keywords) {

                if ($keywords) {

                    if( !strpos($keywords, '.')) {
                        $date = date('Y-m-d', strtotime($keywords));
                    }
                    $query->where('product_requests.brand_model_code', 'like', "%{$keywords}%");
                    if( isset( $date )) {
                        $query->orWhereDate("product_requests.created_at", $date);
                    }

                    return $query;
                }
            })
                ->addIndexColumn();

            return $datatables->make(true);
        }
        return view('platform.product.product_requests_list', compact('title','breadCrum'));
    }
}
