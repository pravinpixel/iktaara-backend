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
            <th> Date </th>
            <th> Order Id </th>
            <th> Tracking Code </th>
            <th> Tracking Message </th>
            <th> Billing Info </th>
            <th> Amount </th>
            <th> Coupon Info</th>
            <th> Qty </th>
            <th> Payment Status </th>
            <th> Order Status </th>
        </tr>
    </thead>
    <tbody>

        @if (isset($list_data) && !empty($list_data))
            @foreach ($list_data as $item)
                <tr>
                    <td>{{ $item->created_at }}</td>
                    <td>{{ $item->order_no }}</td>
                    <td>{{ $item->shipment_tracking_code }}</td>
                    <td>{{ $item->shipment_tracking_message }}</td>
                    <td>{{ $item->billing_info }}</td>
                    <td>{{ $item->amount }}</td>
                    <td>{{ $item->is_coupon }}</td>
                    <td>{{ $item->order_quantity }}</td>
                    <td>{{ $item->payment_status }}</td>
                    <td>{{ $item->status }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
