@if( isset( $from ) && $from == 'pdf')
<style>
    table{ border-spacing: 0;width:100%; }
    table th,td {
        border:1px solid;
    }
</style>
@endif
<table>
    <thead>
        <tr>
            <th>Payment Date</th>
            <th>Order No</th>
            <th>Payment Amount</th>
            <th>Payment No</th>
            <th>Payment Status </th>
        </tr>
    </thead>
    <tbody>
        @if( isset( $list ) && !empty($list))
            @foreach ($list as $item)
            <tr>
                <td>{{ $item->created_at }}</td>
                <td>{{ $item->order_no }}</td>
                <td>{{ $item->paid_amount }}</td>
                <td>{{ $item->payment_no }}</td>
                <td>{{ $item->status }}</td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>
