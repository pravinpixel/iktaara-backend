<?php

namespace App\Http\Controllers\Api;

use App\Models\OrderExchangeReason;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class ExchangeStatusController extends Controller
{
    public function index()
    {
        $data = OrderExchangeReason::where('status', 'published')->orderBy('order_by', 'asc')->get();
        return new Response([
            'error' => 0,
            'status_code' => 200,
            'message' => 'Success',
            'status' => 'success',
            'data' => $data
        ], 200);
    }
}
