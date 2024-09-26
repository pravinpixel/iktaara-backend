<div class="card-toolbar w-100">
    <div class="col-sm-12">
        <h4> Filter Reports </h4>
    </div>
    <form id="search-form">
        <div class="row w-100">
            <div class="col-sm-12 col-md-4 col-lg-4">
                <div class="form-group">
                    <label class="text-muted"> Date Added </label>
                    <input class="form-control form-control-solid w-100" name="date_range" placeholder="Pick date range" id="kt_ecommerce_report_views_daterangepicker" />
                </div>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-6">
                <div class="form-group">
                    <label class="text-muted"> Order No </label>
                    <input type="text" name="filter_search_data" id="filter_search_data" class="form-control">
                </div>
            </div>

            {{-- <div class="col-sm-6 col-md-4 col-lg-2">
                @php
                    $status_array = array('placed', 'shipped', 'delivered', 'cancelled');
                @endphp
                <div class="form-group">
                    <label class="text-muted">Order Status</label>
                    <select name="filter_product_status" id="filter_product_status" class="form-control product-select2">
                        <option value="">All</option>
                        @if( isset( $status_array ) && !empty( $status_array ))
                            @foreach ($status_array as $item)
                                <option value="{{ $item }}" >{{ ucfirst($item) }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div> --}}



            <div class="col-sm-6 col-md-4 col-lg-4">
                <div class="form-group mt-8 text-start">
                    <button type="reset" class="btn btn-sm btn-warning" > Clear </button>
                    <button type="submit" class="btn btn-sm btn-primary" > Submit </button>
                </div>
            </div>
        </div>
    </form>
</div>

