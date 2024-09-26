<style>
    .card .card-header {
        min-height: 42px;
    }
</style>
<div class="card card-flush py-4">
    <form id="add_seller_form" class="form" enctype="multipart/form-data" style="overflow-x: hidden;">

    <div class="pt-0">
        <div class="form-row row mb-10">
            <div class="form-group col-md-6">
                <label for="shop_name" class="pb-3">{{ __('Business / Company / Shop Name') }}<span class="required" aria-required="true"> </span></label>
                <input id="shop_name" type="text" class="form-control form-control-solid @error('first_name') is-invalid @enderror"
                    name="shop_name" value="{{ $merchantShopData->shop_name ?? ''}}" required autocomplete="shop_name" autofocus>
                    @error('shop_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group col-md-6">
                <label for="contact_person" class="pb-3">{{ __('Contact Person') }}<span class="required" aria-required="true"> </span></label>
                <input id="contact_person" type="text" class="form-control form-control-solid @error('contact_person') is-invalid @enderror"
                        name="contact_person" value="{{ $merchantShopData->contact_person ?? '' }}" required autocomplete="contact_person" autofocus>
                    @error('contact_person')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
            </div>
        </div>

        <div class="row form-row mb-10">
            <div class="form-group col-md-6">
                <label for="contact_number" class="pb-3">{{ __('Contact Number') }}<span class="required" aria-required="true"> </span></label>

                <input id="contact_number" type="text" class="form-control form-control-solid @error('contact_number') is-invalid @enderror"
                        name="contact_number" value="{{ $merchantShopData->contact_number ?? '' }}" required autocomplete="contact_number" autofocus>

                @error('contact_number')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group col-md-6">
                <label for="state" class="pb-3">{{ __('State') }}<span class="required" aria-required="true"> </span></label>

                <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select an option" id="seller_state" name="state_id" required>
                    <option value="">Select State</option>
                    @foreach ($states as $state)
                        <option value="{{ $state->id }}" @if (isset($merchantShopData->state_id) && $merchantShopData->state_id == $state->id) selected @endif>
                            {{ $state->state_name }}
                        </option>
                    @endforeach
                </select>
                {{-- <input id="state" type="text" class="form-control @error('state') is-invalid @enderror" name="state" value="{{ old('state') }}" autocomplete="state" autofocus> --}}
            </div>

        </div>
        <div class="row mb-10">
            <div class="form-group col-md-6" style="position: relative;">
                <label for="area" class="col-md-2 col-form-label text-left pb-3">{{ __('Area') }}<span class="required" aria-required="true"></label>
                @if (is_string($merchantShopAreas))
                    <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select an option" id="seller_area" name="area_id">
                        <option value="">Select Area</option>
                    </select>
                    <span class="spinner-border spinner-border-sm seller-area-spinner text-primary"></span>

                @else
                    <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select an option" id="seller_area" name="area_id">
                        <option value="">Select Area</option>
                        @foreach ($merchantShopAreas as $area)
                            <option value="{{ $area->id }}" @if (isset($merchantShopData->area_id) && $merchantShopData->area_id == $area->id) selected @endif>
                                {{ $area->area_name }}
                            </option>
                        @endforeach
                    </select>
                    <span class="spinner-border spinner-border-sm seller-area-spinner text-primary"></span>

                @endif
            </div>

            <div class="form-group col-md-6" style="position: relative;">
                <label for="pincode" class="col-md-2 col-form-label text-left pb-3">{{ __('Pin Code') }}<span class="required" aria-required="true"></label>
                @if (is_string($merchantShopPincodes))
                    <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select an option" id="seller_pincode" name="pincode_id">
                        <option value="">Select Pincode</option>
                    </select>
                    <span class="spinner-border spinner-border-sm seller-pincode-spinner text-primary"></span>

                @else
                    <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select an option" id="seller_pincode" name="pincode_id">
                        <option value="">Select Pincode</option>
                        @foreach ($merchantShopPincodes as $pincode)
                            <option value="{{ $pincode->id }}" @if (isset($merchantShopData->pincode_id) && $merchantShopData->pincode_id == $pincode->id) selected @endif>
                                {{ $pincode->pincode }}
                            </option>
                        @endforeach
                    </select>
                    <span class="spinner-border spinner-border-sm seller-pincode-spinner text-primary"></span>

                @endif
            </div>

<!--
            <label for="desc" class="col-md-2 col-form-label text-left">{{ __('About Company') }}</label>

            <div class="col-md-4">
                <textarea id="desc" type="text" class="form-control @error('desc') is-invalid @enderror" name="desc"
                    autocomplete="desc" autofocus>{{ $merchantShopData->desc ?? '' }}
                </textarea>

            </div> -->
        </div>

    </div>

        <div class="card-footer py-5 text-center" id="kt_activities_footer">
            <div class="text-end px-8">
                <button type="reset" class="btn btn-light btn-lg me-3" id="discard">Discard</button>
                <button type="submit" class="btn btn-primary" data-kt-seller-modal-action="submit">
                    <span class="indicator-label">Save and next</span>
                    <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>

    </form>
</div>

@section('add_on_script')
    <script>

        //Cancel button
        var cancelButton = document.querySelector('#discard');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            Swal.fire({
                text: "Are you sure you would like to cancel?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Yes, cancel it!",
                cancelButtonText: "No, return",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function(result) {
                if (result.value) {
                    // window.location.href = "{{ route('login') }}";
                }
            });
        });
    </script>
@endsection
<script>

    /* Merchant URL */
    var id = "{{ $id }}";
    var base_url = "{{ route('merchants.save') }}";
    var add_url = base_url+'/'+id;

    /* DOM content loading function */

    var KTProductCategory = function() {

        const element = document.getElementById('kt_common_add_form');
        const form = element.querySelector('#add_seller_form');
        const modal = new bootstrap.Modal(element);

        const drawerEl = document.querySelector("#kt_common_add_form");
        const commonDrawer = KTDrawer.getInstance(drawerEl);
        // Init add schedule modal
        var initAddRole = () => {
            // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
            var validator = FormValidation.formValidation(
                form, {
                    fields: {
                        'shop_name': {
                            validators: {
                                notEmpty: {
                                    message: 'Business / Company / Shop name is required'
                                }
                            }
                        },
                        'contact_person': {
                            validators: {
                                notEmpty: {
                                    message: 'Contact person is required'
                                }
                            }
                        },
                        'contact_number': {
                            validators: {
                                notEmpty: {
                                    message: 'Contact number is required'
                                }
                            }
                        },
                        'state_id': {
                            validators: {
                                notEmpty: {
                                    message: 'State is required'
                                }
                            }
                        },
                        'area_id': {
                            validators: {
                                notEmpty: {
                                    message: 'Area is required'
                                }
                            }
                        },
                        'pincode_id': {
                            validators: {
                                notEmpty: {
                                    message: 'Pincode is required'
                                }
                            }
                        },
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        submitButton: new FormValidation.plugins.SubmitButton(),
                        bootstrap: new FormValidation.plugins.Bootstrap5({
                            rowSelector: '.fv-row',
                            eleInvalidClass: '',
                            eleValidClass: ''
                        }),
                        icon: new FormValidation.plugins.Icon({
                            valid: 'fa fa-check',
                            invalid: 'fa fa-times',
                            validating: 'fa fa-refresh',
                        }),
                    }
                }
            );

            // Submit button handler
            const submitButton = element.querySelector('[data-kt-seller-modal-action="submit"]');
            // submitButton.addEventListener('click', function(e) {
            submitButton.addEventListener('click', function (e) {
                // Prevent default button action
                e.preventDefault();
                // Validate form before submit
                if (validator) {
                    validator.validate().then(function(status) {

                        if (status == 'Valid') {
                            var from = $('#from').val();
                            var form = $('#add_seller_form')[0];
                            var formData = new FormData(form);
                            formData.append('from', 'sellerForm');
                            submitButton.setAttribute('data-kt-indicator', 'on');
                            submitButton.disabled = true;
                            //call ajax call
                            $.ajax({
                                url: add_url,
                                type: "POST",
                                data: formData,
                                processData: false,
                                contentType: false,
                                beforeSend: function() {},
                                success: function(res) {

                                    if (res.status === "failed") {
                                        // Remove loading indication
                                        submitButton.removeAttribute('data-kt-indicator');
                                        // Enable button
                                        submitButton.disabled = false;

                                        Swal.fire({
                                            text: res.message,
                                            icon: "error",
                                            buttonsStyling: false,
                                            confirmButtonText: "Ok, got it!",
                                            customClass: {
                                                confirmButton: "btn btn-primary"
                                            }
                                        });
                                    } else {
                                        // if( from != '' ) {
                                        //     getProductCategoryDropdown(res.categoryId);
                                        //     return false;
                                        // }
                                        dtTable.ajax.reload();
                                        Swal.fire({
                                            text: res.message,
                                            icon: "success",
                                            buttonsStyling: false,
                                            confirmButtonText: "Ok, got it!",
                                            customClass: {
                                                confirmButton: "btn btn-primary"
                                            }
                                        }).then(function(result) {
                                            if (result.isConfirmed) {
                                                // commonDrawer.hide();
                                                // Remove loading indication
                                                submitButton.removeAttribute('data-kt-indicator');
                                                // Enable button
                                                submitButton.disabled = false;
                                                const nextTabLink = document.querySelector('[data-bs-toggle="tab"][href="#staturatory_details"]');
                                                if (nextTabLink) {
                                                    nextTabLink.removeAttribute('disabled');
                                                    nextTabLink.click();
                                                }
                                            }
                                        });
                                    }
                                }
                            });

                        } else {
                            // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                            Swal.fire({
                                text: "Sorry, looks like there are some errors detected, please try again.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        }
                    });
                }
            });
        }

        return {
            init: function() {
                initAddRole();
            }
        };
    }();

    // On document ready
    KTUtil.onDOMContentLoaded(function() {
        KTProductCategory.init();
    });


</script>

<script>
    $(document).ready(function () {
        $('#seller_state').select2();
        $('#seller_area').select2();
        $('#seller_pincode').select2();
        /* State filter */
        $('#seller_state').on('change', function () {
            var stateId = $(this).val();

            if (stateId) {
                $('.seller-area-spinner').show();

                $.ajax({
                    url: "{{ route('filterAddress') }}",
                    type: "GET",
                    data: { state_id: stateId },
                    dataType: "json",
                    success: function (data) {
                        $('.seller-area-spinner').hide();

                        $('#seller_area').empty();

                        $('#seller_area').append('<option value="">Select Area</option>');
                        $.each(data.areas, function (key, value) {

                            $('#seller_area').append('<option value="' + value.id + '">' + value.area_name + '</option>');
                        });

                    }
                });
            } else {
                $('#seller_area').empty();
                $('#seller_area').append('<option value="">Select Area</option>');
            }
        });

        /* Area filter */
        $('#seller_area').on('change', function () {
            var stateId = $('#seller_state').val();
            var areaId = $(this).val();

            if (stateId) {
                $('.seller-pincode-spinner').show();

                $.ajax({
                    url: "{{ route('filterAddress') }}",
                    type: "GET",
                    data: {
                            state_id: stateId,
                            area_id: areaId
                        },
                    dataType: "json",
                    success: function (data) {
                        $('.seller-pincode-spinner').hide();

                        $('#seller_pincode').empty();

                        $('#seller_pincode').append('<option value="">Select Pincode</option>');
                        $.each(data.pincodes, function (key, value) {
                            $('#seller_pincode').append('<option value="' + value.id + '">' + value.pincode + '</option>');
                        });
                    }
                });
            } else {

                $('#seller_pincode').empty();
                $('#seller_pincode').append('<option value="">Select Pincode</option>');
            }
        });
    });
</script>
