@if( isset( $from ) && $from == 'pdf')
<style>
    table{ border-spacing: 0;width:100%; }
    table th,td {
        border:1px solid;
    }
  
</style>
@endif

<table>
    <thead >
        <tr >
            <th style="background-color: yellow;width:90px;font-weight:500 ;">SKU</th>
            <th style="background-color: yellow;width:350px;font-weight:500 ;">Product Name</th>
            <th style="background-color: yellow;width:150px;font-weight:500 ;">Category</th>
            <th style="background-color: yellow;width:150px;font-weight:500 ;">Category Tagline</th>
            <th style="background-color: yellow;width:150px;font-weight:500 ;">Category Description</th>
            <th style="background-color: yellow;width:150px;font-weight:500 ;"> Sub-Category </th>
            <th style="background-color: yellow;width:150px;font-weight:500 ;"> Subcategory Tagline </th>
            <th style="background-color: yellow;width:150px;font-weight:500 ;"> Subcategory Description </th>
            <th style="background-color: yellow;width:80px;font-weight:500 ;">Brand</th>
            <th style="background-color: yellow;width:500px;font-weight:500 ;">Short Description</th>
            <th style="background-color: yellow;width:85px;font-weight:500 ;">HSN</th>
            <th style="background-color: yellow;width:300px;font-weight:500 ;">4 Bullet Points</th>
            <th style="background-color: yellow;width:500px;font-weight:500 ;">Long Description</th>
            <th style="background-color: yellow;width:500px;font-weight:500 ;">Technical Specification</th>
            <th style="background-color: yellow;width:85px;font-weight:500 ;">Base Price</th>
            <th style="background-color: yellow;width:100px;font-weight:500 ;">Tax(Incl/Excl)</th>
            <th style="background-color: yellow;width:50px;font-weight:500 ;">Tax%</th>
            <th style="background-color: yellow;width:70px;font-weight:500 ;">MRP</th>
            <th style="background-color: yellow;width:80px;font-weight:500 ;">Discounted Price</th>
            <th style="background-color: yellow;width:100px;font-weight:500 ;">Start Date</th>
            <th style="background-color: yellow;width:90px;font-weight:500 ;">End Date</th>
            <th style="background-color: yellow;width:110px;font-weight:500 ;">Video Shopping</th>
            <th style="background-color: yellow;width:110px;font-weight:500 ;">Featured</th>
            <th style="background-color: yellow;width:250px;font-weight:500 ;">Video Link</th>
            <th style="background-color: yellow;width:350px;font-weight:500 ;">Image</th>
            <th style="background-color:#f4b483;width:80px;font-weight:500 ;">Weight</th>
            <th style="background-color:#f4b483;width:80px;font-weight:500 ;">Width</th>
            <th style="background-color: #f4b483;width:80px;font-weight:500 ;">Height</th>
            <th style="background-color: #f4b483;width:80px;font-weight:500 ;">length</th>
            <th style="background-color: #f4b483;width:150px;font-weight:500 ;">Meta Tag Title</th>
            <th style="background-color: #f4b483;width:400px;font-weight:500 ;">Meta Tag Description</th>
            <th style="background-color: #f4b483;width:150px;font-weight:500 ;">Meta Tag Keywords</th>
            <th style="background-color: #f4b483;width:90px;font-weight:500 ;"> Status</th>
            <th style="background-color: #f4b483;width:90px;font-weight:500 ;">Quantity</th>
            <th style="background-color: #f4b483;width:90px;font-weight:500;">Seller Price</th>
        </tr>
    </thead>
    <tbody>
        @if( isset( $list ) && !empty($list))
            @foreach ($list as $item)
            <tr>
                <td>{{ $item->sku }}</td>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->productCategory->name ?? '' }}</td>
                <td>{{ $item->productCategory->tag_line ?? '' }}</td>
                <td>{{ $item->productCategory->description ?? '' }}</td>
                {{-- @php
                $subcategory = null;
                if ($item->productCategory) {
                    $parent_id = $item->productCategory->id;
                    $subcategory = \App\Models\Product\ProductCategory::where('parent_id', $parent_id)->first();
                }
            @endphp
            
            @if ($subcategory)
                <td>{{ $subcategory->name ?? '' }}</td>
                <td>{{ $subcategory->tag_line ?? '' }}</td>
                <td>{{ $subcategory->description ?? '' }}</td>
            @else --}}
                <td></td>
                <td></td>
                <td></td>
            {{-- @endif --}}
            
                <td>{{ $item->productBrand->brand_name ?? '' }}</td>
                <td>{{ strip_tags($item->description ?? '') }}</td>
                <td>{{ $item->hsn_code ?? '' }}</td>
                <td>{{ strip_tags($item->feature_information ?? '') }}</td>
                <td>{{ strip_tags($item->specification ?? '') }}</td>
                <td>{{ strip_tags($item->technical_information ?? '' )}}</td>
                <td>{{ $item->price ?? ''}}</td>
                @php
                 $gstamount=$item->price /100 *18;
                @endphp
                <td>{{number_format($gstamount, 2)}}</td>
                <td>18%</td>
                <td>{{ $item->mrp ?? ''}}</td>
                <td> {{$item->sale_price ?? ''}}</td>
                <td>{{ $item->sale_start_date ?? ''}}</td>
                <td>{{ $item->sale_end_date ?? ''}}</td>
                <td>{{ $item->has_video_shopping ?? ''}}</td>
                <td>{{ ( isset( $item->is_featured ) && $item->is_featured == 1 ) ? 'Yes' : 'No' }}</td>
                <td>
                    @foreach ($item->productVideoLinks as $videoLink)
                        {{ $videoLink->url ?? '' }}
                    @endforeach
                </td>
                <td style="overflow: hidden">{{ $item->base_image ?? '' }}</td>
                <td>{{$item->productMeasurement->weight ?? ''}}</td>
                <td>{{$item->productMeasurement->width ?? ''}}</td>
                <td>{{$item->productMeasurement->hight ?? ''}}</td>
                <td>{{$item->productMeasurement->length ?? ''}}</td>
                <td>{{ $item->productMeta->meta_title ?? '' }}</td>
                <td>{{ $item->productMeta->meta_description ?? '' }}</td>
                <td>{{ $item->productMeta->meta_keyword ?? '' }}</td>
                <td>{{ $item->status ?? ''}}</td>
                <td>{{ $item->quantity ?? ''}}</td>
                <td>{{ $item->seller_price ?? '' }}</td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>
