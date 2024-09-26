<?php

namespace App\Exports;

use App\Models\Seller\Merchant;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class MerchantExport implements FromView
{
    public function view(): View
    {
        // $list = Order::all();
        $list = Merchant::with('state:id,state_name', 'area:id,area_name', 'pincode:id,pincode')
        ->select('id', 'merchant_no', DB::raw("CONCAT(first_name, ' ', last_name) as name"), 'email', 'mobile_no', 'address', 'state_id', 'terms_conditions', 'status')
        ->get();
        return view('platform.exports.merchants.excel', compact('list'));
    }
}
