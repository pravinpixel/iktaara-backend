<?php

namespace App\Exports;

use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PaymentExport implements FromView
{
    public function view(): View
    {
        $list = Payment::selectRaw('mm_orders.order_no, mm_payments.status as payment_status, mm_payments.*, sum(mm_order_products.quantity) as order_quantity')
                ->join('orders', 'orders.id', '=', 'payments.order_id')
                ->join('order_products', 'order_products.order_id', '=', 'orders.id')
                ->groupBy('orders.id')->orderBy('orders.id', 'desc')->get();

        return view('platform.payment.list._excel', compact('list'));
    }
}
