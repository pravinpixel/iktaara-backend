<?php
$brandArray = [];
if(!empty($merchantProfitBrandData)){
$brandArray = $merchantProfitBrandData->pluck('brand_margin_value', 'brand_id')->toArray();
}
?>
<div class="card card-flush py-4">
    <div class="pt-0" style="height: 50vh;overflow-y: auto; overflow-x:hidden">
        <div class="row mb-3">
            @foreach($brands as $index => $brand)

                <label for="margin_value" class="col-md-4 col-form-label text-center">Margin value %</label>

                <div class="col-md-4">
                    <input id="margin_value" data-margin="{{$brand->profit_margin_percent}}" type="text" class="{{isset($merchantProfitBrandData[$index]) ? $merchantProfitBrandData[$index]->brand_id : 'not'}} form-control form-control-solid @error('brand_margin') is-invalid @enderror"
                        name="brand_margin_value[{{ $brand->id }}]"
                        @if (!empty($merchantProfitBrandData) && in_array($brand->id, array_column($merchantProfitBrandData->toArray(), 'brand_id')))
                        value="{{ $brandArray[$brand->id] ?? '' }}"
                        @else
                        value="{{ $brand->profit_margin_percent }}"
                        @endif
                        required autocomplete="brand_margin" autofocus>
                </div>
                <label for="brand_name" class="col-md-4 col-form-label text-center" style="padding-bottom: 30px;">{{ $brand->brand_name }}</label>
            @endforeach
        </div>
    </div>
</div>
