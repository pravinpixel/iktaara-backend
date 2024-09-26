<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Reviews;
use App\Http\Controllers\Controller;
use App\Models\OrderProduct;
use Illuminate\Support\Facades\Validator;

class ReivewsController extends Controller
{
    public $status = 'succes';
    public $message = '';
    public $errors = [];

    public function create(Request $request, Reviews $reviews)
    {

        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'product_id' => 'required',
            'rating' => 'required',
            'comments' => 'required',
        ]);

        if ($validator->fails()) {
            $this->status = 'failed';
            $this->errors = $validator->errors();
            $this->message = $validator->errors()->first();
        } else {
            $previousOrder = OrderProduct::where('product_id', $request->product_id)
                // ->join('orders', 'orders.id', '=', 'order_products.order_id')
                ->where('order_id', $request->order_id)
                // ->where('orders.customer_id', auth()->guard('api')->user()->id)
                ->first();

            $previousReview = Reviews::where('product_id', $request->product_id)
                ->where('order_id', $request->order_id)
                ->where('customer_id', auth()->guard('api')->user()->id)
                ->count();

            if ($previousOrder != null) {
                $this->status = 'failed';
                $this->message = 'This action is not allowed';
            }

            if ($previousReview != 0) {
                $this->status = 'failed';
                $this->message = 'We have received your feedback already';
            }

            if (isset($previousOrder->status) && (($previousOrder->status == 'delivered') || ($previousOrder->status == 5))) {
                $res = $reviews->fill([
                    'customer_id' => auth()->guard('api')->user()->id,
                    'order_id' => $request->order_id,
                    'product_id' => $request->product_id,
                    'star' => $request->rating,
                    'comments' => $request->comments,
                    'ip' => $request->ip(),
                    'status' => 0,
                ])->save();
                if ($res) {
                    $this->status = 'success';
                    $this->message = 'Thank you for your valuable feedback';
                } else {
                    $this->status = 'failed';
                    $this->message = 'Please Try again';
                }
            } else {
                $this->status = 'failed';
                $this->message = 'Your Order was not Delivered';
            }
        }

        if ($this->status == 'success') {
            return response()->json([
                'status_code' => 201,
                'status' => $this->status,
                'message' => $this->message,
                'data' => ''
            ], 201);
        } else {
            return response()->json([
                'status_code' => 201,
                'status' => $this->status,
                'message' => $this->message,
                'data' => [],
            ], 201);
        }
    }
}
