<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderCancelReason;
use Illuminate\Http\Response;

class OrderCancelController extends Controller
{
    public function index()
    {
        $data = OrderCancelReason::where('status','published')->orderBy('order_by','asc')->get();
        $error = 0;
        $message = 'Success';
        $status = "success";
        $status_code = '200';
        return new Response(array('error' => $error,'status_code' => $status_code, 'message' => $message, 'status' => $status, 'data' => $data), $status_code);
    }
}
