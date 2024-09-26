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
            <th>Merchant No</th>
            <th>Name</th>
            <th>Email</th>
            <th>Mobile</th>
            {{-- <th>Address</th> --}}
            <th>State</th>
            <th>Status</th>

        </tr>
    </thead>
    <tbody>
        @if( isset( $list ) && !empty($list))
            @foreach ($list as $item)
            <tr>
                <td>{{ $item->merchant_no }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->mobile_no }}</td>
                <td>{{ $item->state->state_name }}</td>
                <td>{{  $item->status }}</td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>
