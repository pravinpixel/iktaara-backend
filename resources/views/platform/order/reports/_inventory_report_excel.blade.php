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
            <th> Product Name </th>
            <th> Primary Category</th>
            <th> Secondary Category</th>
            <th> Brand / Manufacturer </th>
            <th> Product Availability Count </th>
            <th> MRP/SSP</th>
            <th> Stock Status </th>
        </tr>
    </thead>
    <tbody>
        @if (isset($list) && !empty($list))
            @foreach ($list as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>
                        @php
                            $category = App\Models\Product\ProductCategory::find($item->primary_category);
                        @endphp
                        {{ $category ? $category->name : '' }}</td>
                    <td>{{ $item->secondary_category }}</td>
                    <td>{{ $item->brand_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->mrp }}</td>
                    <td>
                        {{ ucwords(str_replace('_', ' ', $item->stock_status)) }} </td>

                </tr>
            @endforeach
        @endif
    </tbody>
</table>
