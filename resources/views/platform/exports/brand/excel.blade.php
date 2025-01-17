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
            <th>Added Date</th>
            <th>Brand Name</th>
            <th>Short Description</th>
            <th>Notes</th>
            <th>Profit Percentage</th>
            <th>Order By</th>
            <th>Added By</th>
            <th>Status</th>

        </tr>
    </thead>
    <tbody>
        @if( isset( $list ) && !empty($list))
            @foreach ($list as $item)
            <tr>
                <td>{{ $item->created_at }}</td>
                <td>{{ $item->brand_name }}</td>
                <td>{{ $item->short_description }}</td>
                <td>{{ $item->notes }}</td>
                <td>{{ $item->profit_margin_percent }}</td>
                <td>{{ $item->order_by }}</td>
                <td>{{ $item->users_name }}</td>
                <td>{{  $item->status }}</td>

            </tr>
            @endforeach
        @endif
    </tbody>
</table>
