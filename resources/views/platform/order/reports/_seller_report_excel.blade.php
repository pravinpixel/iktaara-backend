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
            <th> Seller Name </th>
                                <th> Seller Status</th>
                                <th> Contact Person</th>
                                <th> Phone </th>
                                <th> City </th>
                                <th> State</th>
                                <th> Pincode</th>
                                <th> Order on hand</th>
        </tr>
    </thead>
    <tbody>
        @if( isset( $list ) && !empty($list))
            @foreach ($list as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->status }}</td>
                <td>{{ $item->contact_person }}</td>
                <td>{{ $item->mobile_no }}</td>
                <td>{{ $item->city }}</td>
                <td>{{ $item->state_name }}</td>
                <td>{{ $item->pincode }}</td>
                <td>{{ $item->order_on_hand }}</td>

                <td>
                    @php
                         $seller_name = '';
                    if(isset($item->assigned_seller_2)){
                        $seller_name = App\Models\Seller\Merchant::getMerchantName($item->assigned_seller_2);
                    }elseif(isset($item->assigned_seller_1)){
                        $seller_name = App\Models\Seller\Merchant::getMerchantName($item->assigned_seller_1);

                    }
                    @endphp
                    {{ $seller_name }} </td>
                <td>
                    @php
                        $seller_location = '';
                    if($item->assigned_seller_2){
                        $seller_location = App\Models\Seller\Merchant::getMerchantLocation($item->assigned_seller_2);
                    }elseif($item->assigned_seller_1){
                        $seller_location = App\Models\Seller\Merchant::getMerchantLocation($item->assigned_seller_1);

                    }
                    @endphp
                    {{ $seller_location }}</td>
                <td> {{ $item->billing_name }}</td>
                <td> {{ $item->customer_zone }}</td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>
