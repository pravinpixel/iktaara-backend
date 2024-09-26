<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuAllResource;
use App\Http\Resources\MenuResource;
use App\Models\Product\ProductCategory;
use App\Services\ShipRocketService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MenuController extends Controller
{

    public function getTopMenu(Request $request)
    {
        $slug           = $request->slug;

        $data           = ProductCategory::where(['is_home_menu' => 'yes', 'status' => 'published'])
            ->when($slug != '', function ($q) use ($slug) {
                return $q->where('slug', $slug);
            })
            ->when($slug == '', function ($q) {
                return $q->where('parent_id', 0);
            })
            ->orderBy('order_by', 'asc')
            ->get();
        $error = 0;
        $message = 'Success';
        $status = "success";
        $status_code = '200';
        $data_collection = MenuAllResource::collection($data);
        return new Response(array('error' => $error,'status_code' => $status_code, 'message' => $message, 'status' => $status, 'data' => $data_collection), $status_code);
    }

    public function getAllMenu(ShipRocketService $shipService)
    {
        // $token_response = $shipService->getToken();
        // $token_decode_response = json_decode($token_response );

        // dd( $token_decode_response->token );
        // dd( $ShipRocketService->getToken() );
        $menus   = ProductCategory::select('id', 'name', 'slug')->where(['status' => 'published', 'parent_id' => 0])->orderBy('order_by', 'asc')->get();
        $error = 0;
        $message = 'Success';
        $status = "success";
        $status_code = '200';
        $data_collection = MenuAllResource::collection($menus);
        return new Response(array('error' => $error,'status_code' => $status_code, 'message' => $message, 'status' => $status, 'data' => $data_collection), $status_code);
    }
}
