@if (isset($from) && $from == 'pdf')
    <style>
        table {
            border-spacing: 0;
            width: 100%;
        }

        table th,
        td {
            border: 1px solid;
        }
    </style>
@endif
<table>
    <thead>
        <tr>
            <th> Order Id </th>
            <th> Order Date</th>
            <th> Quantity</th>
            <th> Value </th>
            <th> Order Status </th>
            <th> Seller Name</th>
        </tr>
    </thead>
    <tbody>
        @if (isset($list) && !empty($list))
            @foreach ($list as $item)
                <tr>
                    <td>{{ $item->order_no }}</td>
                    <td>{{ $item->created_at }}</td>
                    <td>{{ $item->order_quantity }}</td>
                    <td>{{ $item->order_value }}</td>
                    <td>
                        @php
                            $order_status = App\Models\Master\OrderStatus::where('id', $item->order_status)
                                ->select('status_name')
                                ->pluck('status_name')
                                ->first();

                        @endphp
                        {{ ucwords($order_status) }}</td>
                    <td>
                        @php
                            $seller_name = '';
                            if (isset($item->assigned_seller_2)) {
                                $seller_name = App\Models\Seller\Merchant::getMerchantName($item->assigned_seller_2) ? App\Models\Seller\Merchant::getMerchantName($item->assigned_seller_2) : 'Not Assigned';
                            } elseif (isset($item->assigned_seller_1)) {
                                $seller_name = App\Models\Seller\Merchant::getMerchantName($item->assigned_seller_1) ? App\Models\Seller\Merchant::getMerchantName($item->assigned_seller_1) : 'Not Assigned' ;
                            } else {
                                $seller_name = 'Not assigned';
                            }
                        @endphp
                        {{ $seller_name }} </td>

                </tr>
            @endforeach
        @endif
    </tbody>
</table>
