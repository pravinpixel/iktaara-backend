<!--begin::Header-->
<div class="card-header" id="kt_activities_header">
    <h3 class="card-title fw-bolder text-dark">{{ $modal_title ?? 'Form Action' }}</h3>
    <div class="card-toolbar">
        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary me-n5" id="kt_activities_close">
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
<style media="screen">
    figure.zoom {
        position: relative;
        border: 5px solid white;
        box-shadow: -1px 5px 15px black;
        height: 250px;
        width: 500px;
        overflow: hidden;
        cursor: zoom-in;
    }

    figure.zoom img:hover {
        opacity: 0;
    }

    figure.zoom img {
        transition: opacity 0.5s;
        display: block;
        width: 100%;
    }
</style>
<!--end::Header-->
<!--begin::Body-->
<form id="add_product_attribute_form" class="form" action="#" enctype="multipart/form-data">

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
                        <input type="hidden" name="from" id="from" value="{{ $from ?? '' }}">


                        <div class="fv-row mb-7">
                            <label class="required fw-bold fs-6 mb-2">Zone Name</label>
                            <input type="text" name="zone_name" class="form-control form-control-solid mb-3 mb-lg-0"
                                placeholder="Zone Name" value="{{ $info->zone_name ?? '' }}" />
                        </div>
                        <div class="fv-row mb-7">
                            <label class=" fw-bold fs-6 mb-2">Choose State</label>
                            <select name="state[]" id="state" aria-label="Select a State" multiple="multiple"
                                data-placeholder="Select a State..." class="form-select mb-2" required>
                                <option value=""></option>
                                @isset($states)

                                    @foreach ($states as $item)
                                        <option value="{{ $item->id }}"
                                            @if (isset($info->collectionStates) &&
                                                    in_array($item->id, array_column($info->collectionStates->toArray(), 'state_id'))) selected="selected" @endif>
                                            {{-- <option value="{{ $item->id }}" > --}}
                                            {{ $item->state_name }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div class="fv-row mb-7">
                            <label class="required fw-bold fs-6 mb-2">Order of zone</label>
                            <input type="number" name="zone_order" class="form-control form-control-solid mb-3 mb-lg-0"
                                placeholder="Order of Zone" value="{{ $info->zone_order ?? '' }}" />
                        </div>
                        <div class="col-md-3">
                            <div class="mb-7">
                                <label class="fw-bold fs-6 mb-2"> Published </label>
                                <div
                                    class="form-check form-switch form-check-custom form-check-solid fw-bold fs-6 mb-2">
                                    <input class="form-check-input" type="checkbox" name="status"
                                        value="1" @if (isset($info->status) && $info->status == 'published') checked @endif />
                                </div>
                            </div>
                        </div>
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
            <button type="submit" class="btn btn-primary" data-kt-order_status-modal-action="submit">
                <span class="indicator-label">Submit</span>
                <span class="indicator-progress">Please wait...
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </div>
    </div>
</form>

<style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>

<script>
    $(document).ready(function() {
        $.fn.select2.amd.require(['select2/selection/search'], function(Search) {
            var oldRemoveChoice = Search.prototype.searchRemoveChoice;

            Search.prototype.searchRemoveChoice = function() {
                oldRemoveChoice.apply(this, arguments);
                this.$search.val('');
            };
            $('#state').select2({

                // width:'300px'
            });
        });
        var collection_product = $("#state").val();

        //  $('#collection_product').select2({    placeholder: 'This is my placeholder',   allowClear: true});



        var add_url = "{{ route('zone.save') }}";

        // Class definition
        var KTUsersAddRole = function() {
            // Shared variables
            const element = document.getElementById('kt_common_add_form');
            const form = element.querySelector('#add_product_attribute_form');
            const modal = new bootstrap.Modal(element);

            const drawerEl = document.querySelector("#kt_common_add_form");
            const commonDrawer = KTDrawer.getInstance(drawerEl);


            // Init add schedule modal
            var initAddRole = () => {

                // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
                var validator = FormValidation.formValidation(
                    form, {
                        fields: {
                            'zone_name': {
                                validators: {
                                    notEmpty: {
                                        message: 'Zone is required'
                                    }
                                }
                            },
                            'state': {
                                validators: {
                                    notEmpty: {
                                        message: 'State is required'
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
                const submitButton = element.querySelector(
                    '[data-kt-order_status-modal-action="submit"]');
                // submitButton.addEventListener('click', function(e) {
                $('#add_product_attribute_form').submit(function(e) {
                    // Prevent default button action
                    e.preventDefault();
                    // Validate form before submit
                    if (validator) {
                        validator.validate().then(function(status) {
                            if (status == 'Valid') {

                                var formData = new FormData(document.getElementById(
                                    "add_product_attribute_form"));
                                submitButton.setAttribute('data-kt-indicator', 'on');
                                // Disable button to avoid multiple click
                                submitButton.disabled = true;
                                var from = $('#from').val();
                                //call ajax call
                                $.ajax({
                                    url: add_url,
                                    type: "POST",
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    beforeSend: function() {},
                                    success: function(res) {
                                        console.log(res.views);
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
                                            if (!from) {
                                                dtTable.ajax.reload();
                                            }

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
                                                    if (res.from) {}

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

        function zoom(e) {
            var zoomer = e.currentTarget;
            e.offsetX ? offsetX = e.offsetX : offsetX = e.touches[0].pageX
            e.offsetY ? offsetY = e.offsetY : offsetX = e.touches[0].pageX
            x = offsetX / zoomer.offsetWidth * 100
            y = offsetY / zoomer.offsetHeight * 100
            zoomer.style.backgroundPosition = x + '% ' + y + '%';
        }

        function getBannercollection(id) {

            var data = ImageBanner.find(item => item.id == id);

            if (Object.keys(data).length === 0) {

            } else {
                $('#collecion_img').show();
                document.getElementById('zoom-main').style.backgroundImage = `url(${data.image})`;
                document.getElementById('dynamicImageUrl').src = data.image;
            }

        }

        function checkMapDiscount(ev) {
            if (ev.checked) {
                $('#collecion_img').hide();
                $('#order-pane-input').show();
                $('#order-pane-select').hide();
                $('#order-label').html('Sort Order');
            } else {
                $('#collecion_img').show();
                $('#order-pane-input').hide();
                $('#order-pane-select').show();
                $('#order-label').html('Home Page Section');

            }
        }

        $('#state').change(function() {
            var id = $(this).val();
            $('#area').find('option').not(':first').remove();
            $.ajax({
                url: 'getAreas/' + id,
                type: 'get',
                dataType: 'json',
                success: function(response) {
                    var len = 0;
                    if (response['data'] != null) {
                        len = response['data'].length;
                    }
                    if (len > 0) {
                        for (var i = 0; i < len; i++) {
                            var id = response['data'][i].id;
                            var name = response['data'][i].area_name;
                            var option = "<option value='" + id + "'>" + name + "</option>";
                            $("#area").append(option);
                        }
                    }
                }
            });
        });
        $('#area').change(function() {
            var id = $(this).val();
            $('#pincode').find('option').not(':first').remove();
            $.ajax({
                url: 'getPincodes/' + id,
                type: 'get',
                dataType: 'json',
                success: function(response) {
                    var len = 0;
                    if (response['data'] != null) {
                        len = response['data'].length;
                    }
                    if (len > 0) {
                        for (var i = 0; i < len; i++) {
                            var id = response['data'][i].id;
                            var name = response['data'][i].pincode;
                            var option = "<option value='" + id + "'>" + name + "</option>";
                            $("#pincode").append(option);
                        }
                    }
                }
            });
        });
    });
</script>
