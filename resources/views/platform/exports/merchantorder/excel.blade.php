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
            <th>Date</th>
            <th style="width:50%">OrderId</th>
            <th>Order Amount</th>
            <th>Qty</th>
            <th>Payment Status</th>
            <th>Order Status</th>
            <th>Product Value</th>
            <th>Product Margin</th>
            <th>Profit Margin</th>
            <th>Merchant Order Status</th>
            <th>Assigned Merchant 1</th>
            <th>Assigned Merchant 2</th>
            <th>Assigned to merchant</th>
        </tr>
    </thead>
    <tbody>
     {{-- @php
         
   dd($data);
      @endphp --}}
            @foreach ($modifiedData as $item)
            <tr>
                <td>{{$item['created_at']}}</td>
                <td>{{$item['order_no']}}</td>
                <td>{{$item['total_amount'] }}</td>
                <td>{{$item['order_quantity'] }}</td>
                <td>{{$item['payment_status'] }}</td>
                <td>{{$item['status_name'] }}</td>
                <td>{{$item['total_amount']}}</td>
                <td>{{$item['profit_margin']}}</td>
                <td>{{$item['merchant_profit_margin']}}</td>
                <td>{{$item['merchant_order_status']}}</td>
                <td>{{$item['merchant_no']}}</td>
                <td>{{$item['assigned_seller_2']}}</td>
                <td>{{$item['assigned_to_merchant']}}</td>
            </tr>
            @endforeach

    </tbody>
</table>
