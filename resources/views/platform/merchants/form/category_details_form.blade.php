<?php
if(!empty($merchantProfitCategoryData)){
    $categoryArray = $merchantProfitCategoryData->pluck('category_margin_value', 'category_id')->toArray();
}
?>
<div class="card card-flush py-4">
    <div class="pt-0" style="height: 50vh;overflow-y: auto; overflow-x:hidden">
        <div class="row mb-3">
            @foreach($categories as $index => $category)
                <label for="margin" class="col-md-4 col-form-label text-center">Margin value %</label>

                <div class="col-md-4">
                    <input id="margin_value" type="text" class="form-control form-control-solid @error('margin_val') is-invalid @enderror"
                        name="category_margin_value[{{$category->id}}]"
                        @if (!empty($merchantProfitBrandData) && in_array($category->id, array_column($merchantProfitCategoryData->toArray(), 'category_id')))
                        value="{{ $categoryArray[$category->id] ?? '' }}"
                        @elseif (empty($categoryArray))
                        value="{{ $category->profit_margin_percent }}"
                        @endif
                        required autocomplete="first_name" autofocus>
                </div>
                <label for="category_name" class="col-md-4 col-form-label text-center" style="padding-bottom: 30px;">{{ $category->name }}</label>
            @endforeach
        </div>
    </div>
</div>


