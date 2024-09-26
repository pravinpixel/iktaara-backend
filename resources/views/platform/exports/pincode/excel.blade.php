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
            <th>Pincode</th>
            <th>State</th>
            <th>Area</th>

        </tr>
    </thead>
    <tbody>
        @if( isset( $list ) && !empty($list))
            @foreach ($list as $item)
            <tr>
                <td>{{ $item->pincode }}</td>
                <td>{{ $item->state_name }}</td>
                <td>{{ $item->area_name }}</td>

            </tr>
            @endforeach
        @endif
    </tbody>
</table>
