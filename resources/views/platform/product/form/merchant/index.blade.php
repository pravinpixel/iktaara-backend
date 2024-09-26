<!--begin::Variations-->
<div class="card card-flush py-4">
    <!--begin::Card header-->
    <div class="card-header">
        <div class="card-title w-100">

            <h2 class="w-100">
                Merchants
            </h2>


        </div>
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Input group-->
        <div class="" data-kt-ecommerce-catalog-add-product="auto-options">
            <!--begin::Label-->
            <!--end::Label-->
            <!--begin::Repeater-->
            <div id="kt_ecommerce_add_product_options">
                <!--begin::Form group-->
                <div class="form-group">
                    <div class="row d-flex justify-content-center" style="overflow-y: auto;">
                        @if (is_string($product_available_details))
                            <div class="col-md-4">
                                <div class="mb-10 fv-row">
                                    <label class="form-label">Merchant Name</label>
                                    <input type="text" name="merchant_name" class="form-control mb-2 readOnly" placeholder="Merchant Name" value="{{ $product->merchant_id ?? '' }}" readonly/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-10 fv-row">
                                    <label class="form-label">Available product quantity</label>
                                    <input type="text" name="hsn_code" class="form-control mb-2" placeholder="Product Quantity" value="{{ $product->qty ?? '' }}" readonly/>
                                </div>
                            </div>
                        @else
                            @foreach($product_available_details as $product)
                            <div class="col-md-12">
                                <div class="col-md-4">
                                    <div class="mb-10 fv-row">
                                        <label class="form-label">Merchant Name: {{ $product->merchant->first_name.' '.$product->merchant->last_name ?? '' }}</label>

                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-10 fv-row">
                                        <label class="form-label">Available product quantity: {{ $product->qty ?? '' }}</label>

                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @endif

                    </div>
                </div>

            </div>
            <!--end::Repeater-->
        </div>
        <!--end::Input group-->
    </div>
    <!--end::Card header-->
</div>
<!--end::Variations-->

<script>
    // $("body").on("click", ".removeUrlRow", function () {
    //     $(this).parents(".childUrlRow").remove();
    // })
</script>
