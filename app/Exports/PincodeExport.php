<?php

namespace App\Exports;


use App\Models\Master\Pincode;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class PincodeExport implements FromView
{
    public function view(): View
    {
        $list = Pincode::join('areas', 'areas.id', '=', 'pincodes.area_id')->join('states', 'states.id', '=', 'pincodes.state_id')->get();
        return view('platform.exports.pincode.excel', compact('list'));
    }
}
