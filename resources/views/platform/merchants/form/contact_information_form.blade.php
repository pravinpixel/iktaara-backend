<style>
    .card .card-header {
        min-height: 42px;
    }
</style>
<div class="card card-flush py-4">
    <form id="add_contact_form" class="form" enctype="multipart/form-data" style="overflow-x: hidden;">

    <div class=" pt-0">
        <div class="row mb-10">

            <label for="first_name" class="col-md-2 col-form-label text-left">{{ __('First Name') }}<span class="required" aria-required="true"> </span></label>

            <div class="col-md-4">
                <input id="first_name" type="text" class="form-control form-control-solid @error('first_name') is-invalid @enderror"
                    name="first_name" value="{{ $merchantViewData->first_name ?? ''}}" required autocomplete="first_name" autofocus>
            </div>
            @error('first_name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror

            <label for="first_name" class="col-md-2 col-form-label text-left">{{ __('Last Name') }}</label>

            <div class="col-md-4">
                <input id="last_name" type="text" class="form-control form-control-solid @error('last_name') is-invalid @enderror"
                    name="last_name" value="{{ $merchantViewData->last_name ?? ''}}" autocomplete="last_name" autofocus>
            </div>

        </div>

        <div class="row mb-10">

            <label for="mobile_no" class="col-md-2 col-form-label text-left">{{ __('Mobile Number') }}<span class="required" aria-required="true"> </span></label>

            <div class="col-md-4">
                <input id="mobile_no" type="text" class="form-control form-control-solid @error('mobile_no') is-invalid @enderror"
                    name="mobile_no" value="{{ $merchantViewData->mobile_no ?? '' }}" required autocomplete="mobile_no" autofocus>
            </div>

            @error('mobile_no')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror

            <label for="email" class="col-md-2 col-form-label text-left">{{ __('Email Address') }}<span class="required" aria-required="true"> </span></label>

            <div class="col-md-4">
                <input id="email" type="email" class="form-control form-control-solid @error('email') is-invalid @enderror"
                    name="email" value="{{ $merchantViewData->email ?? '' }}" required autocomplete="email">
            </div>

            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <input id="password" type="hidden" class="form-control form-control-solid" name="merchant_password" value="Merchant@2023">

        {{-- <div class="row mb-10">
            <label for="password" class="col-md-2 col-form-label text-left">{{ __('Password') }}<span class="required" aria-required="true"> </span></label>

            <div class="col-md-4">
                <input id="password" type="password" class="form-control form-control-solid @error('password') is-invalid @enderror"
                    name="password" autocomplete="new-password">

            </div>

            <label for="password-confirm" class="col-md-2 col-form-label text-left">{{ __('Confirm Password') }}<span class="required" aria-required="true"> </span></label>

            <div class="col-md-4">
                <input id="password-confirm" type="password" class="form-control form-control-solid" name="password_confirmation"
                    autocomplete="new-password">
            </div>
        </div> --}}
        <div class="row mb-10">
            <label for="address" class="col-md-2 col-form-label text-left">{{ __('Address') }}</label>

            <div class="col-md-4">
                <textarea id="address" type="text" class="form-control form-control-solid @error('address') is-invalid @enderror" rows="3" name="address"
                    autocomplete="address" autofocus>{{ $merchantViewData->address ?? '' }}
                </textarea>

            </div>

            <label for="city" class="col-md-2 col-form-label text-left">{{ __('City') }}</label>

            <div class="col-md-4">
                {{--<select class="form-select form-select-solid" data-control="select2" data-placeholder="Select an option" id="city" name="city">
                    @foreach ($city as $key => $val)
                        <option value="{{ $val->id }}" @if (isset($merchantViewData->city_id) && $merchantViewData->city_id == $val->id)selected @endif>{{ $val->city }}</option>
                    @endforeach
                </select> --}}
                <input id="city" type="text" class="form-control form-control-solid @error('city') is-invalid @enderror"
                    name="city" value="{{ $merchantViewData->city ?? '' }}" autocomplete="city" autofocus>


            </div>

        </div>
        <div class="row mb-10">
            <label for="state" class="col-md-2 col-form-label text-left">{{ __('State') }}<span class="required" aria-required="true"> </span></label>

            <div class="col-md-4">
                <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select an option" id="state" name="state_id">
                    <option value="">Select State</option>
                    @foreach ($states as $state)
                        <option value="{{ $state->id }}" @if (isset($merchantViewData->state_id) && $merchantViewData->state_id == $state->id) selected @endif>
                            {{ $state->state_name }}
                        </option>
                    @endforeach
                </select>
                {{-- <input id="state" type="text" class="form-control @error('state') is-invalid @enderror" name="state" value="{{ old('state') }}" autocomplete="state" autofocus> --}}
            </div>

            <label for="area" class="col-md-2 col-form-label text-left">{{ __('Area') }}<span class="required" aria-required="true"> </span></label>
            <div class="col-md-4" style="position: relative;">
                @if (is_string($merchantAreas))
                    <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select an option" id="area" name="area_id">
                        <option value="">Select Area</option>
                    </select>
                    <span class="spinner-border spinner-border-sm contact-area-spinner text-primary"></span>

                @else
                    <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select an option" id="area" name="area_id">
                        <option value="">Select Area</option>
                            @foreach ($merchantAreas as $area)
                                <option value="{{ $area->id }}" @if (isset($merchantViewData->area_id) && $merchantViewData->area_id == $area->id) selected @endif>
                                    {{ $area->area_name }}
                                </option>
                            @endforeach
                    </select>
                    <span class="spinner-border spinner-border-sm contact-area-spinner text-primary"></span>

                @endif
            </div>

        </div>
        <div class="row mb-10">

            <label for="pincode" class="col-md-2 col-form-label text-left">{{ __('Pin Code') }}<span class="required" aria-required="true"> </span></label>
            <div class="col-md-4" style="position: relative;">
                @if (is_string($merchantPincodes))
                    <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select an option" id="pincode" name="pincode_id">
                        <option value="">Select Pincode</option>
                    </select>
                    <span class="spinner-border spinner-border-sm contact-pincode-spinner text-primary"></span>

                @else
                    <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select an option" id="pincode" name="pincode_id">
                        <option value="">Select Pincode</option>
                            @foreach ($merchantPincodes as $pincode)
                                <option value="{{ $pincode->id }}" @if (isset($merchantViewData->pincode_id) && $merchantViewData->pincode_id == $pincode->id) selected @endif>
                                    {{ $pincode->pincode }}
                                </option>
                            @endforeach
                    </select>
                    <span class="spinner-border spinner-border-sm contact-pincode-spinner text-primary"></span>

                @endif
            </div>

            <label for="desc" class="col-md-2 col-form-label text-left">{{ __('About Company') }}</label>

            <div class="col-md-4">
                <textarea id="desc" type="text" class="form-control form-control-solid @error('desc') is-invalid @enderror" rows="7" name="desc"
                    autocomplete="desc" autofocus>{{ $merchantViewData->desc ?? '' }}
                </textarea>

            </div>
        </div>

    </div>


        <div class="card-footer py-5 text-center" id="kt_activities_footer">
            <div class="text-end px-8">
                <button type="reset" class="btn btn-light btn-lg me-3" id="discard">Discard</button>
                <button type="submit" class="btn btn-primary" data-kt-contact-modal-action="submit">
                    <span class="indicator-label">Save and Next</span>
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

    $('.numberonly').keypress(function (e) {
        var charCode = (e.which) ? e.which : event.keyCode
        if (String.fromCharCode(charCode).match(/[^0-9]/g))
            return false;
    });

    // $('#is_parent').change(function(){
    //     if($("#is_parent").prop('checked') == true){
    //         $('#parent-tab').addClass('d-none');
    //     } else {
    //         $('#parent-tab').removeClass('d-none');
    //     }
    // });

    // $('#is_tax').change(function(){
    //     if($("#is_tax").prop('checked') == true){
    //         $('#tax-tab').removeClass('d-none');
    //     } else {
    //         $('#tax-tab').addClass('d-none');
    //     }
    // });

    //image image script
    //  document.getElementById('readUrl').addEventListener('change', function() {
    //     // console.log("111");
    //     if (this.files[0]) {
    //         var picture = new FileReader();
    //         picture.readAsDataURL(this.files[0]);
    //         picture.addEventListener('load', function(event) {
    //             console.log(event.target);
    //             let img_url = event.target.result;
    //             $('#manual-image').css({
    //                 'background-image': 'url(' + event.target.result + ')'
    //             });
    //         });
    //     }
    // });
    // document.getElementById('avatar_remove_logo').addEventListener('click', function() {
    //     $('#image_remove_image').val("yes");
    //     $('#manual-image').css({
    //         'background-image': ''
    //     });
    // });

    // //medium image
    // document.getElementById('mediumFile').addEventListener('change', function() {
    //     // console.log("111");
    //     if (this.files[0]) {
    //         var picture = new FileReader();
    //         picture.readAsDataURL(this.files[0]);
    //         picture.addEventListener('load', function(event) {
    //             console.log(event.target);
    //             let img_url = event.target.result;
    //             $('#medium-image').css({
    //                 'background-image': 'url(' + event.target.result + ')'
    //             });
    //         });
    //     }
    // });
    // document.getElementById('medium_remove_logo').addEventListener('click', function() {
    //     $('#image_remove_medium').val("yes");
    //     $('#medium-image').css({
    //         'background-image': ''
    //     });
    // });

    // //small image
    // document.getElementById('smallImage').addEventListener('change', function() {
    //     // console.log("111");
    //     if (this.files[0]) {
    //         var picture = new FileReader();
    //         picture.readAsDataURL(this.files[0]);
    //         picture.addEventListener('load', function(event) {
    //             console.log(event.target);
    //             let img_url = event.target.result;
    //             $('#small-image').css({
    //                 'background-image': 'url(' + event.target.result + ')'
    //             });
    //         });
    //     }
    // });
    // document.getElementById('medium_remove_logo').addEventListener('click', function() {
    //     $('#image_remove_small').val("yes");
    //     $('#small-image').css({
    //         'background-image': ''
    //     });
    // });

    /* Merchant URL */
    var id = "{{ $id }}";
    var base_url = "{{ route('merchants.save') }}";
    if(id){
        var add_url = base_url+'/'+id;
    }else{
        var add_url = base_url;
        console.log(add_url, "add rl");
    }

    /* DOM content loading function */

    var KTProductCategory = function() {

        const element = document.getElementById('kt_common_add_form');
        const form = element.querySelector('#add_contact_form');
        const modal = new bootstrap.Modal(element);

        const drawerEl = document.querySelector("#kt_common_add_form");
        const commonDrawer = KTDrawer.getInstance(drawerEl);
        // Init add schedule modal
        var initAddRole = () => {
            // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
            var validator = FormValidation.formValidation(
                form, {
                    fields: {
                        'first_name': {
                            validators: {
                                notEmpty: {
                                    message: 'First name is required'
                                }
                            }
                        },
                        'mobile_no': {
                            validators: {
                                notEmpty: {
                                    message: 'Mobile no is required'
                                }
                            }
                        },
                        'email': {
                            validators: {
                                notEmpty: {
                                    message: 'Email is required'
                                },
                                emailAddress: {
                                    message: 'The value is not a valid email address'
                                }
                            }
                        },
                        'password': {
                            validators: {
                                notEmpty: {
                                    message: 'Password is required'
                                },
                                stringLength: {
                                    min: 8,
                                    max: 20,
                                    message: 'The password must be between 8 and 20 characters'
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
            // Cancel button handler
            // const cancelButton = element.querySelector('#discard');
            // cancelButton.addEventListener('click', e => {
            //     e.preventDefault();

            //     Swal.fire({
            //         text: "Are you sure you would like to cancel?",
            //         icon: "warning",
            //         showCancelButton: true,
            //         buttonsStyling: false,
            //         confirmButtonText: "Yes, cancel it!",
            //         cancelButtonText: "No, return",
            //         customClass: {
            //             confirmButton: "btn btn-primary",
            //             cancelButton: "btn btn-active-light"
            //         }
            //     }).then(function(result) {
            //         if (result.value) {
            //             commonDrawer.hide(); // Hide modal
            //         }
            //     });
            // });

            // Submit button handler
            const submitButton = element.querySelector('[data-kt-contact-modal-action="submit"]');
            // submitButton.addEventListener('click', function(e) {
            submitButton.addEventListener('click', function (e) {
                // Prevent default button action
                e.preventDefault();
                // Validate form before submit
                if (validator) {
                    validator.validate().then(function(status) {

                        if (status == 'Valid') {
                            // var from = $('#from').val();
                            var form = $('#add_contact_form')[0];
                            var formData = new FormData(form);
                            formData.append('from', 'contactForm');
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
                                                const nextTabLink = document.querySelector('[data-bs-toggle="tab"][href="#seller_location"]');
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
        /* State filter */
        $('#state').on('change', function () {
            var stateId = $(this).val();

            if (stateId) {
                $('.contact-area-spinner').show();

                $.ajax({
                    url: "{{ route('filterAddress') }}",
                    type: "GET",
                    data: { state_id: stateId },
                    dataType: "json",
                    success: function (data) {
                        $('.contact-area-spinner').hide();

                        $('#area').empty();

                        $('#area').append('<option value="">Select Area</option>');
                        $.each(data.areas, function (key, value) {

                            $('#area').append('<option value="' + value.id + '">' + value.area_name + '</option>');
                        });

                    }
                });
            } else {
                $('#area').empty();
                $('#area').append('<option value="">Select Area</option>');
            }
        });

        /* Area filter */
        $('#area').on('change', function () {
            var stateId = $('#state').val();
            var areaId = $(this).val();

            if (stateId) {
                $('.contact-pincode-spinner').show();

                $.ajax({
                    url: "{{ route('filterAddress') }}",
                    type: "GET",
                    data: { 
                            state_id: stateId,
                            area_id: areaId
                        },
                    dataType: "json",
                    success: function (data) {
                        $('.contact-pincode-spinner').hide();

                        $('#pincode').empty();

                        $('#pincode').append('<option value="">Select Pincode</option>');
                        $.each(data.pincodes, function (key, value) {
                            $('#pincode').append('<option value="' + value.id + '">' + value.pincode + '</option>');
                        });
                    }
                });
            } else {

                $('#pincode').empty();
                $('#pincode').append('<option value="">Select Pincode</option>');
            }
        });
    });
</script>



