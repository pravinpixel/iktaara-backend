<?php echo '<?xml version="1.0" encoding="UTF-8" ?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://www.iktaraa.com/buy</loc>
    </url>
@if (isset($pages) && !empty($pages))
@foreach ($pages as $page)
    <url>
        <loc>{{ $page }}</loc>
    </url>
@endforeach
@endif
{{-- @if (isset($brands) && !empty($brands))
@foreach ($brands as $brand)
    <url>
        <loc>https://iktaraa.com/brands/{{ $brand->slug }}</loc>
    </url>
@endforeach
@endif --}}
@foreach ($products as $items)
    <url>
        <loc>https://www.iktaraa.com/buy/product/{{ $items->product_url }}</loc>
        <lastmod>{{ $items->created_at->tz('UTC')->toAtomString() }}</lastmod>
    </url>
@endforeach

@foreach ($tmp as $items)
    <url>
        <loc>https://www.iktaraa.com/buy/category/{{ $items['slug'] }}</loc>
        <lastmod>{{ $items['created_at']->tz('UTC')->toAtomString() }}</lastmod>
    </url>
    @if (isset($items['child']) && (!empty($items['child'])))
    @foreach ($items['child'] as $sub_category)
    <url>
        <loc>https://www.iktaraa.com/buy/category/{{ $items['slug'] }}/{{$sub_category['slug']}}</loc>
        <lastmod>{{ $sub_category['created_at']->tz('UTC')->toAtomString() }}</lastmod>
    </url>
    @if (isset($sub_category['innerchild']) && (!empty($sub_category['innerchild'])))
    @foreach ($sub_category['innerchild'] as $ssub_category)
    <url>
        <loc>https://www.iktaraa.com/buy/category/{{ $items['slug'] }}/{{$sub_category['slug']}}/{{$ssub_category['slug']}}</loc>
        <lastmod>{{ $ssub_category['created_at']->tz('UTC')->toAtomString() }}</lastmod>
    </url>
    @endforeach

    @endif
    @endforeach

    @endif
@endforeach
</urlset>
