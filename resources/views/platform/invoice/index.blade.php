<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<body>
    <style>
        body {
            border: 1px solid #ddd;
        }

        table td {
            font-size: 11px;
        }

        .header-table,
        .item-table {
            width: 100%;
        }

        .header-table td,
        th {
            border: 1px solid #ddd;
            border-collapse: collapse;
            padding: 5px;
        }

        .item-table td,
        .item-table th {
            border: 1px solid #ddd;
            border-collapse: collapse;
            padding: 5px;
        }

        .total-amount-table td,
        .total-amount-table th {
            padding: 5px;
        }

        .no-border td,
        th {
            border: none;
            width: 100%;
            font-size: 13px;
            color: #000000;
        }

        .w-70 {
            width: 70%;
        }

        .w-50 {
            width: 50%;
        }
        .w-35 {
            width: 35%;
        }
        .w-30 {
            width: 60%;
        }
        .w-25{
            width: 30%;
        }
        .w-10{
            width: 10%;
        }

        .w-40 {
            width: 50%;
        }
        .w-20{
            width: 20%;
        }

        .p-5 {
            padding: 5px;
        }
    </style>
    <table class="header-table" cellspacing="0" padding="0">

        {{-- {{ dd($globalInfo) }} --}}
        <tr>
            <td colspan="2">
                <table class="no-border" style="width: 100%">
                    <tr>
                        <td class="w-25"> <span><img src="{{ public_path('assets/logo/logo.png') }}" alt=""
                                    height="100"></span> </td>
                        <td class="w-30">
                        <div style="font-size:8px;width:100%;padding-top:20px;"><b>TWELFTHROOT SOUNDS PRIVATE LIMITED</b></div>
                        <div style="text-align:left;margin-left:10px;padding-top:20px;">
                        73, Anna Salai,Chennai-600002<br>
                        support@iktaraa.com<br>
                        7845205333
                        </div>
                        <div style="text-align:left;margin-left:10px;font-size:10px;">
                        GST NO:33AAKCT0154DIZR</div>
                        </td>
                        <!-- <td class="w-30">
                            <h3> {{ $globalInfo->site_name }} </h3>
                            <div> {{ $globalInfo->address }} </div>
                            <div> {{ $globalInfo->site_email }} </div>
                            <div> {{ $globalInfo->site_mobile_no }} </div>
                            {{-- <div> GSTIN: 33334DS22SD34FHJ63A </div> --}}
                        </td> -->
                        <td class="w-10">
                            <h1>Tax Invoice</h1>
                        </td>
                    </tr>
                </table>
            </td>

        </tr>
        <tr>
            <td class="w-70">
                <table class="no-border" style="width: 100%">
                    <tr>
                        <td class="w-50">
                            <h3> Billing Details </h3>
                            <div><b>{{ $order_info->billing_name }}</b></div>
                            <div>{{ $order_info->billing_address_line1 }}</div>
                            <div>{{ $order_info->billing_city }}</div>
                            <div>{{ $order_info->billing_state }}</div>
                            <div>{{ $order_info->billing_post_code }}</div>
                            <div>{{ $order_info->billing_mobile_no }}</div>
                            <div>{{ $order_info->billing_email }}</div>
                        </td>

                        <td class="w-50">
                            <h3> Shipping Details </h3>
                            <div><b>{{ $order_info->shipping_name }}</b></div>
                            <div>{{ $order_info->shipping_address_line1 }}</div>
                            <div>{{ $order_info->shipping_city }}</div>
                            <div>{{ $order_info->shipping_state }}</div>
                            <div>{{ $order_info->shipping_post_code }}</div>
                            <div>{{ $order_info->shipping_mobile_no }}</div>
                            <div>{{ $order_info->shipping_email }}</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="w-30">

                <table class="no-border w-100">
                    <tr>
                        <td class="w-50"> Invoice Date </td>
                        <td class="w-50"> {{ date('d/m/Y', strtotime($order_info->created_at)) }} </td>
                    </tr>
                    <tr>
                        <td class="w-50"> Invoice No </td>
                        <td class="w-50"> {{ $order_info->order_no }} </td>
                    </tr>
                    <tr>
                        <td class="w-50"> Payment Status </td>
                        <td class="w-50"> {{ $order_info->payments->status }} </td>
                    </tr>
                </table>
            </td>
        </tr>


    </table>
    <table class="item-table" cellspacing="0" padding="0">
        <tr>
            <th style="width: 8px;" rowspan="2">S.No</th>
            <th rowspan="2" > Items</th>
            <th rowspan="2"> HSN</th>
            <th rowspan="2"> Reference</th>
            <th rowspan="2"> Qty</th>
            <th rowspan="2"> MRP</th>
            <th rowspan="2"> Discount </th>
            <th rowspan="2"> Rate </th>
            @if (isset($order_info->billing_state) && $order_info->billing_state == 'Tamil Nadu')
                <th colspan="2"> CGST </th>
                <th colspan="2"> SGST </th>
                <th rowspan="2"> Amount </th>
        </tr>
        <tr>
            <th>%</th>
            <th>Amt</th>
            <th>%</th>
            <th>Amt</th>
        </tr>
    @else
        <th colspan="2"> IGST </th>
        <th rowspan="2"> Amount </th>
        </tr>
        <tr>
            <th>%</th>
            <th>Amt</th>
        </tr>
        @endif
        @if (isset($order_info->orderItems) && !empty($order_info->orderItems))
            @php
                $i = 1;
            @endphp
            @foreach ($order_info->orderItems as $item)
                <tr>
                    @php
                    $price = $item->price;
                    $precentage=$item->mrp /100 *18;
                     $mrp = round($precentage + $item->mrp);
                    $formattedPrice = number_format($price, 2);
                     $discoum_amount=round($mrp - $price);
                @endphp
                    <td>{{ $i }}</td>
                    <td>
                        {{ $item->product_name }}
                    </td>
                    <td> {{ $item->hsn_code }}</td>
                    <td> {{ $item->sku }} </td>
                    <td> {{ $item->quantity }}</td>
                    @if($discoum_amount >= 1)
                    <td>
                        {{ number_format($item->sub_total  + $discoum_amount, 2) }}
                   </td>
                   @else
                    <td>
                        {{ number_format($item->sub_total, 2) }}
                   </td>
                 @endif

               @if($discoum_amount >= 1)
               <td>{{number_format($discoum_amount,2)}}</td>
           @else
               <td>0</td>
           @endif
           @php
           $formattedNumber =number_format($item->price - $item->tax_amount, 2) ; // Example number
           $numberWithoutComma = str_replace(',', '', $formattedNumber);
           $roundedNumber = round($numberWithoutComma);
           @endphp
                  @php
                  $i++;
                  $rate=intval($roundedNumber);
                  @endphp
                          <td>{{number_format($rate)}}.00</td>
                    @if (isset($order_info->billing_state) && $order_info->billing_state == 'Tamil Nadu')
                        <td>{{ $item->tax_percentage / 2 }}%</td>
                        <td>{{ number_format(round($item->tax_amount / 2), 2) }}</td>
                        <td>{{ $item->tax_percentage / 2 }}%</td>
                        <td>{{ number_format(round($item->tax_amount / 2), 2) }}</td>
                        <td>{{ number_format($item->sub_total, 2) }}</td>
                    @else
                        <td>{{ $item->tax_percentage }}%</td>
                        <td>{{ number_format(round($item->tax_amount), 2) }}</td>
                        <td>{{ number_format($item->sub_total, 2) }}</td>
                    @endif
                </tr>

            @endforeach
        @endif
    </table>
    <table class="item-table" cellspacing="0" padding="0">
        <tr>
            <td style="padding-top:10px;width:50%;border-bottom:none;">
                <div>
                    <label for="">Total in words </label>
                </div>
                <div>
                    <b>{{ ucwords(getIndianCurrency($order_info->amount)) }}</b>
                </div>
                <div style="margin-top: 10px;">
                    Thank you for the payment. You just made our day
                </div>
            </td>
            <td style="width: 50%;">
                <table class="no-border" cellspacing="0" padding="0" style="width: 100%;">
                    <tr>
                        <td style="text-align: right">
                            <div>Sub Total </div>
                            {{-- <small>(Tax inclusive)</small> --}}
                        </td>
                        <td style="text-align: right"><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>
                            {{ number_format($order_info->sub_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right">Tax ({{ (int) $order_info->tax_percentage }}%) </td>
                        <td style="text-align: right"><span
                                style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>{{ number_format($order_info->tax_amount, 2) }}
                        </td>
                    </tr>
                    @if ($order_info->coupon_amount > 0)
                        <tr>
                            <td style="text-align: right">
                                <div>Discount Amount </div>
                                <small>( {{ $order_info->coupon_code }})</small>
                            </td>
                            <td style="text-align: right"><span
                                    style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>{{ number_format($order_info->coupon_amount, 2) }}
                            </td>
                        </tr>
                    @endif

                    @if ($order_info->discount_amount > 0)
                        <tr>
                            <td style="text-align: right">
                                <div>Discount Amount </div>
                            </td>
                            <td style="text-align: right"><span
                                    style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>{{ number_format($order_info->discount_amount, 2) }}
                            </td>
                        </tr>
                    @endif
                    @if ($order_info->shipping_amount > 0)
                        <tr>
                            <td style="text-align: right">
                                <div>Shipping Fee </div>
                                {{-- <small>( {{ $order_info->shipping_type }})</small> --}}
                            </td>
                            <td style="text-align: right"><span
                                    style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>{{ number_format($order_info->shipping_amount, 2) }}
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="text-align: right;font-weight:700;font-size:15px;">Total</td>
                        <td style="text-align: right;font-weight:700;font-size:15px;">
                            <span
                                style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>{{ number_format($order_info->amount, 2) }}
                        </td>
                    </tr>
                    <tr>

                        <td colspan="2 " style="text-align: center;border-top:1px solid #ddd">
                            <div style="margin-top: 100px">Authorized Signature</div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
