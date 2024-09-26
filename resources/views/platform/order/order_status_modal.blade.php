<!--begin::Header-->
<div class="card-header" id="kt_activities_header">
    <h3 class="card-title fw-bolder text-dark">{{ $modal_title ?? 'Form Action' }}</h3>
    <div class="card-toolbar">
        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary me-n5 close-modal-button"
            id="kt_activities_close">
            <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
            <span class="svg-icon svg-icon-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                        transform="rotate(-45 6 17.3137)" fill="currentColor" />
                    <rect x="7.41422" y="6" width="16" height="2" rx="1"
                        transform="rotate(45 7.41422 6)" fill="currentColor" />
                </svg>
            </span>
            <!--end::Svg Icon-->
        </button>
    </div>
</div>
<!--end::Header-->
<!--begin::Body-->
<form id="change_order_status_form" class="form" action="#" enctype="multipart/form-data">
    <div class="card-body position-relative" id="kt_activities_body">
        <div id="kt_activities_scroll" class="position-relative scroll-y me-n5 pe-5" data-kt-scroll="true"
            data-kt-scroll-height="auto" data-kt-scroll-wrappers="#kt_activities_body"
            data-kt-scroll-dependencies="#kt_activities_header, #kt_activities_footer" data-kt-scroll-offset="5px">
            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_update_role_scroll">
                <div class="fv-row mb-10">
                    <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_add_user_scroll"
                        data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                        data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header"
                        data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">
                        <input type="hidden" name="id" value="{{ $info->id ?? '' }}">
                        <div class="row justify-content-center py-8 px-8 py-md-10 px-md-0">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table border border-secondary border-5" style="width:100%;">
                                        <thead style="border-bottom: 5px solid #E4E6EF;">
                                            <tr>
                                                <th style="font-weight: bold;text-align:center;"
                                                    class="pl-0 text-muted text-uppercase">
                                                    Ordered Items
                                                </th>
                                                <th style="font-weight: bold;text-align:center;"
                                                    class="text-right font-weight-bold text-muted text-uppercase">
                                                    Qty
                                                </th>
                                                <th style="font-weight: bold;text-align:center;"
                                                    class="text-right font-weight-bold text-muted text-uppercase">
                                                    Unit Price
                                                </th>
                                                <th style="font-weight: bold;text-align:center;"
                                                    class="text-right pr-0 font-weight-bold text-muted text-uppercase">
                                                    status</th>
                                                <th style="font-weight: bold;text-align:center;"
                                                    class="merchant_list_header text-right pr-0 font-weight-bold text-muted text-uppercase">
                                                    Assign Seller</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($order_product) && !empty($order_product))
                                                @foreach ($order_product as $index => $item)
                                                    <input type="hidden" name="item_id-{{ $index }}"
                                                        value="{{ $item->id ?? '' }}">
                                                    <tr class="font-weight-boldest">
                                                    <tr>
                                                        <td class="border-0 pl-0 pt-7 d-flex align-items-center"
                                                            style="padding-left:10px;">
                                                            {{ $item->product_name }}</td>
                                                        <td class="text-right pt-7 align-middle"
                                                            style="text-align:center;vertical-align:top !important;">
                                                            {{ $item->quantity }}
                                                        </td>
                                                        <td class="text-right pt-7 align-middle"
                                                            style="text-align:center;vertical-align:top !important;">
                                                            {{ $item->sub_total }}
                                                        </td>
                                                        <td class="text-primary pr-0 pt-7 text-right align-middle"
                                                            style="text-align:center;vertical-align:top !important;">
                                                            <div class="col-9" style="margin: 0 auto;">
                                                                <select
                                                                    class="form-select form-select-solid order-status-details"
                                                                    data-control="select2"
                                                                    data-placeholder="Select an option"
                                                                    name="order_status_id-{{ $index }}"
                                                                    id="order_status_id">
                                                                    <option value="">--select--</option>
                                                                    @if (isset($order_status_info) && !empty($order_status_info))
                                                                        @foreach ($order_status_info as $item_status)
                                                                            <option value="{{ $item_status->id }}"
                                                                                @if ($item->status == $item_status->id) selected @endif>
                                                                                {{ $item_status->status_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>


                                                        </td>

                                                        @if ($item->assigned_to_merchant == 'not_assigned')
                                                            <td class="text-primary pr-0 pt-7 text-right align-middle"
                                                                style="text-align:center;vertical-align:top !important;">
                                                                {{-- <label>Assign Seller</label> --}}
                                                                <select class="form-select merchant_list"
                                                                    data-control="select2"
                                                                    data-placeholder="Select a merchant"
                                                                    name="merchant_list_id-{{ $index }}"
                                                                    id="merchant_list_id">
                                                                    <option value="">--select--</option>

                                                                    @foreach ($merchants_list_to_assign as $merchant)
                                                                        <option value="{{ $merchant->id }}">
                                                                            {{ $merchant->merchant_no . '-' . $merchant->first_name . ' ' . $merchant->last_name }}
                                                                        </option>
                                                                    @endforeach

                                                                </select>
                                                            </td>
                                                        @endif
                                                        <td id="shipment-track"
                                                            style="text-align:center;display:none;padding-top:23px;">
                                                            <div class="col-10">
                                                                <input type="text" class="form-control mb-2"
                                                                    placeholder="Shipment Tracking code"
                                                                    value="{{ $item->shipment_tracking_code ?? '' }}"
                                                                    name="shipment_tracking_code-{{ $index }}"
                                                                    id="shipment_tracking_code">

                                                                <textarea class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Shipment tracking message"
                                                                    name="shipment_tracking_message-{{ $index }}" id="shipment_tracking_message" cols="30"
                                                                    rows="5">{{ $item->shipment_tracking_message ?? '' }}</textarea>

                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr style="border-bottom: 2px solid #E4E6EF;">
                                                        <td colspan="4">

                                                            <p style="padding-left:10px;"><b>Track Order: </b></p>
                                                            <div class="mt-10 mb-10" style="padding:10px;">
                                                                <div class="d-flex justify-content-center align-items-center"
                                                                    style="margin:20px;margin-bottom:20px;">
                                                                    @php
                                                                        $orderStatuses = $item->getTracking($item->order_id, $item->product_id);
                                                                    @endphp
                                                                    @foreach ($orderStatuses as $order)
                                                                        @if ($order->action === 'Order Placed')
                                                                            <div class="text-center"">
                                                                                <img class="track-order"
                                                                                    src="https://dashboard.iktaraa.com/storage/order_status/order_placed.png"
                                                                                    alt="">
                                                                                <p class="fs-8" style="margin:0;">
                                                                                    {{ $order->action }}</p>
                                                                                <p class="fs-8"style="margin:0;">
                                                                                    {{ \Illuminate\Support\Carbon::parse($order->created_at)->format('h:i A, d M Y') }}
                                                                                </p>
                                                                            </div>
                                                                        @endif

                                                                        @if ($order->action === 'Order Confirmed')
                                                                            <div class="line">
                                                                            </div>
                                                                            <div class="text-center"">
                                                                                <img class="track-order"
                                                                                    src="https://dashboard.iktaraa.com/storage/order_status/order_confirmed.png"
                                                                                    alt="">
                                                                                <p class="fs-8" style="margin:0;">
                                                                                    {{ $order->action }}</p>
                                                                                <p class="fs-8" style="margin:0;">
                                                                                    {{ \Illuminate\Support\Carbon::parse($order->created_at)->format('h:i A, d M Y') }}
                                                                                </p>
                                                                            </div>
                                                                        @endif

                                                                        @if ($order->action === 'Order Shipped')
                                                                            <div class="line">
                                                                            </div>
                                                                            <div class="text-center"">
                                                                                <img class="track-order"
                                                                                    src="https://dashboard.iktaraa.com/storage/order_status/order_shipped.png"
                                                                                    alt="">
                                                                                <p class="fs-8" style="margin:0;">
                                                                                    {{ $order->action }}</p>
                                                                                <p class="fs-8" style="margin:0;">
                                                                                    {{ \Illuminate\Support\Carbon::parse($order->created_at)->format('h:i A, d M Y') }}
                                                                                </p>
                                                                            </div>
                                                                        @endif
                                                                        @if ($order->action === 'Order Delivered')
                                                                            <div class="line">
                                                                            </div>
                                                                            <div class="text-center"">
                                                                                <img class="track-order"
                                                                                    src="https://dashboard.iktaraa.com/storage/order_status/order_delivered.png"
                                                                                    alt="">
                                                                                <p class="fs-8" style="margin:0;">
                                                                                    {{ $order->action }}</p>
                                                                                <p class="fs-8" style="margin:0;">
                                                                                    {{ \Illuminate\Support\Carbon::parse($order->created_at)->format('h:i A, d M Y') }}
                                                                                </p>
                                                                            </div>
                                                                        @endif
                                                                        @if ($order->action === 'Exchange Request')
                                                                            <div class="line">
                                                                            </div>
                                                                            <div class="text-center"">
                                                                                <img class="track-order"
                                                                                    src="https://dashboard.iktaraa.com/storage/order_status/exchange_requested.png"
                                                                                    alt="">
                                                                                <p class="fs-8" style="margin:0;">
                                                                                    {{ $order->action }}</p>
                                                                                <p class="fs-8" style="margin:0;">
                                                                                    {{ \Illuminate\Support\Carbon::parse($order->created_at)->format('h:i A, d M Y') }}
                                                                                </p>
                                                                            </div>
                                                                        @endif
                                                                        @if ($order->action === 'Exchange Accepted')
                                                                            <div class="line">
                                                                            </div>
                                                                            <div class="text-center"">
                                                                                <img class="track-order"
                                                                                    src="https://dashboard.iktaraa.com/storage/order_status/exchange_accepted.png"
                                                                                    alt="">
                                                                                <p class="fs-8" style="margin:0;">
                                                                                    {{ $order->action }}</p>
                                                                                <p class="fs-8" style="margin:0;">
                                                                                    {{ \Illuminate\Support\Carbon::parse($order->created_at)->format('h:i A, d M Y') }}
                                                                                </p>
                                                                            </div>
                                                                        @endif
                                                                        @if ($order->action === 'Exchange Rejected')
                                                                            <div class="line">
                                                                            </div>
                                                                            <div class="text-center"">
                                                                                <img class="track-order"
                                                                                    src="https://dashboard.iktaraa.com/storage/order_status/exchange_rejected.png"
                                                                                    alt="">
                                                                                <p class="fs-8" style="margin:0;">
                                                                                    {{ $order->action }}</p>
                                                                                <p class="fs-8" style="margin:0;">
                                                                                    {{ \Illuminate\Support\Carbon::parse($order->created_at)->format('h:i A, d M Y') }}
                                                                                </p>
                                                                            </div>
                                                                        @endif
                                                                        @if ($order->action === 'Exchange Exchanged')
                                                                            <div class="line">
                                                                            </div>
                                                                            <div class="text-center"">
                                                                                <img class="track-order"
                                                                                    src="https://dashboard.iktaraa.com/storage/order_status/exchanged.png"
                                                                                    alt="">
                                                                                <p class="fs-8" style="margin:0;">
                                                                                    {{ $order->action }}</p>
                                                                                <p class="fs-8" style="margin:0;">
                                                                                    {{ \Illuminate\Support\Carbon::parse($order->created_at)->format('h:i A, d M Y') }}
                                                                                </p>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach

                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    </tr>
                                                    <tr>
                                                        {{-- @if (isset($item->tracking) && !empty($item->tracking))
                                                            @foreach ($item->tracking as $track_data)
                                                                {{$track_data->action}}->
                                                            @endforeach
                                                        @endif --}}
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>






    <div class="card-footer py-5 text-center" id="kt_activities_footer">
        <div class="text-end px-8">
            <button type="reset" class="btn btn-light me-3" id="discard">Discard</button>
            <button type="submit" class="btn btn-primary" data-kt-customer-modal-action="submit">
                <span class="indicator-label">Submit</span>
                <span class="indicator-progress">Please wait...
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </div>
    </div>
</form>

<script>
    var add_url = "{{ route('order.change.status') }}";

    // Class definition
    var KTUsersAddRole = function() {
        // Shared variables
        const element = document.getElementById('kt_common_add_form');
        const form = element.querySelector('#change_order_status_form');
        const modal = new bootstrap.Modal(element);

        const drawerEl = document.querySelector("#kt_common_add_form");
        const commonDrawer = KTDrawer.getInstance(drawerEl);


        // Init add schedule modal
        var initAddRole = () => {

            // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
            var validator = FormValidation.formValidation(
                form, {
                    fields: {
                        'order_status_id': {
                            validators: {
                                notEmpty: {
                                    message: 'Order Status is required'
                                }
                            }
                        },

                        'description': {
                            validators: {
                                notEmpty: {
                                    message: 'Description is required'
                                }
                            }
                        },

                    },

                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap: new FormValidation.plugins.Bootstrap5({
                            rowSelector: '.fv-row',
                            eleInvalidClass: '',
                            eleValidClass: ''
                        })
                    }
                }
            );

            // Cancel button handler
            const cancelButton = element.querySelector('#discard');
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
                        commonDrawer.hide(); // Hide modal
                    }
                });
            });

            // Submit button handler
            const submitButton = element.querySelector('[data-kt-customer-modal-action="submit"]');
            // submitButton.addEventListener('click', function(e) {
            $('#change_order_status_form').submit(function(e) {
                // Prevent default button action
                e.preventDefault();
                // Validate form before submit
                if (validator) {
                    validator.validate().then(function(status) {
                        if (status == 'Valid') {

                            var formData = new FormData(document.getElementById(
                                "change_order_status_form"));
                            var formDataObject = {};
                            var groupObject = [];

                            //Convert formdata format to obejct key value pair
                            formData.forEach(function(value, key) {
                                formDataObject[key] = value;
                            });

                            //Group the key pair value to grouped array
                            for (var key in formDataObject) {
                                var suffixMatch = key.match(/\d+$/)
                                if (suffixMatch) {
                                    var suffix = suffixMatch[0];
                                    var baseKey = key.replace("-" + suffix, "");
                                    if (!groupObject[suffix]) {
                                        groupObject[suffix] = {}
                                    }
                                    if (!groupObject[suffix].id) {
                                        groupObject[suffix] = {
                                            id: formDataObject.id
                                        };
                                    }
                                    groupObject[suffix][baseKey] = formDataObject[key]
                                }
                            }

                            formData.append("order_product_details", JSON.stringify(
                                groupObject));

                            submitButton.setAttribute('data-kt-indicator', 'on');
                            // Disable button to avoid multiple click
                            submitButton.disabled = true;

                            //call ajax call
                            $.ajax({
                                url: add_url,
                                type: "POST",
                                // data: formData,
                                data: formData,
                                processData: false,
                                contentType: false,
                                beforeSend: function() {},
                                success: function(res) {
                                    if (res.error == 1) {
                                        // Remove loading indication
                                        submitButton.removeAttribute(
                                            'data-kt-indicator');
                                        // Enable button
                                        submitButton.disabled = false;
                                        let error_msg = res.message
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
                                            if (result
                                                .isConfirmed) {
                                                commonDrawer
                                                    .hide();

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
            // Public functions
            init: function() {
                initAddRole();
            }
        };
    }();

    // On document ready

    KTUtil.onDOMContentLoaded(function() {
        KTUsersAddRole.init();
    });


    //Filter dynamically generate select dropdown

    $(document).ready(function() {

        // Function to update options based on the selected value

        function updateOptions() {

            $('select.form-select-solid').each(function() {

                var selectedValue = $(this).val();
                var select = $(this);

                $('option', select).each(function() {
                    var value = $(this).val();
                    // Your logic here

                    if (selectedValue === '4') {
                        $("#shipment-track").show()
                        if (value === '5') {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    } else if (selectedValue === '2') {
                        if (value === '8') {
                            $(this).show();
                        } else if (value === '3') {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    } else if (selectedValue === '8') {

                        if (value === '4') {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    } else {
                        if (value !== '0') {

                            $(this).hide();
                        }
                    }
                    // else if (selectedValue === '5') {
                    //     if (value !== '0') {
                    //         $(this).hide();
                    //     }
                    // }
                });
            });
        }

        //Call the function for filter options

        updateOptions();

        // Select any options on the dropdown

        $(document).on('change', 'select.form-select-solid', function() {
            updateOptions();

        });
    });

    //Order list reload when the modal is close
    $(document).ready(function() {
        $('#merchant_list_id').select2();
        var orderTable = $('#order-table').DataTable();

        $('.close-modal-button').on('click', function() {
            orderTable.ajax.reload();
        });
        if (!$('.merchant_list').length) // use this if you are using id to check
        {
            $('.merchant_list_header').hide()
        }else{
            $('.merchant_list_header').show()
        }

    });
</script>
