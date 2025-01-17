<style>
    .card .card-header {
        min-height: 42px;
    }
</style>

<div class="card card-flush py-4">
    <form id="add_customer_form" class="form" action="#" enctype="multipart/form-data"  style="overflow-x: hidden;">
        @csrf
        <input type="hidden" name="id" value="{{ $info->id ?? '' }}">
        <div class="row">
            <div class="col-md-6">
                <div class="fv-row mb-7">
                    <label class="required fw-bold fs-6 mb-2">First Name</label>
                    <input type="text" name="first_name" class="form-control form-control-solid mb-3 mb-lg-0"
                        placeholder="First Name" value="{{ $info->first_name ?? '' }}" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="fv-row mb-7">
                    <label class="fw-bold fs-6 mb-2">Last Name</label>
                    <input type="text" name="last_name" class="form-control form-control-solid mb-3 mb-lg-0"
                        placeholder="Last Name" value="{{ $info->last_name ?? '' }}" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="fv-row mb-7">
                    <label class="required fw-bold fs-6 mb-2">Email</label>
                    <input type="email" name="email" class="form-control form-control-solid mb-3 mb-lg-0"
                        placeholder="Email" value="{{ $info->email ?? '' }}" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="fv-row mb-7">
                    <label class="required fw-bold fs-6 mb-2">Mobile Number</label>
                    <input type="text" name="mobile_no" min="0" maxlength="10" class="form-control form-control-solid mb-3 mb-lg-0 mobile_num"
                        placeholder="Mobile Number" value="{{ $info->mobile_no ?? '' }}" />
                </div>
            </div>
        </div>


        <div class="row">
            @if (!isset($info->id))
            <div class="col-md-6">
                <div class="fv-row mb-7">
                    <label class="required fw-bold fs-6 mb-2">Password</label>
                    <input type="password" name="password" class="form-control form-control-solid mb-3 mb-lg-0"
                        placeholder="Password" value="{{ $info->password ?? '' }}" />
                </div>
            </div>
            @endif

            <div class="col-md-6">

                <div class="fv-row mb-7">
                    <label class="fw-bold fs-6 mb-2">DOB</label>
                    <input type="text" name="dob" id="dob" class="form-control form-control-solid mb-3 mb-lg-0"
                        placeholder="DOB" value="{{ $info->dob ?? '' }}"  />
                </div>

            </div>

        </div>


        <div class="row">
            <div class="col-md-6">
                <div class="fv-row mb-7">
                    <label class="fw-bold fs-6 mb-2">Address</label>
                        <textarea   class="form-control form-control-solid  " placeholder="Address" name="address_line1" id="address_line1" cols="30" rows="5" >{{ $default_customer_address->address_line1 ?? '' }}</textarea>     
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-4">

                    <div class="fv-row mb-7">
                        <label class="d-block fw-bold fs-6 mb-5">Image</label>

                        <div class="form-text">Allowed file types: png, jpg,
                            jpeg.</div>
                    </div>
                    <input id="image_remove_image" type="hidden" name="image_remove_image" value="no">
                    <div class="image-input image-input-outline manual-image" data-kt-image-input="true"
                        style="background-image: url({{ asset('userImage/no_Image.png') }})">
                        @if ($info->profile_image ?? '')
                        @php
                            $path = Storage::url($info->profile_image,'public')
                        @endphp
                            <div class="image-input-wrapper w-125px h-125px manual-image"
                                id="manual-image"
                                style="background-image: url({{ asset($path) }});">

                            </div>
                        @else
                            <div class="image-input-wrapper w-125px h-125px manual-image"
                                id="manual-image"
                                style="background-image: url({{ asset('userImage/no_Image.png') }});">
                            </div>
                        @endif
                        <label
                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="change" data-bs-toggle="tooltip"
                            title="Change avatar">
                            <i class="bi bi-pencil-fill fs-7"></i>
                            <input type="file" name="avatar" id="readUrl"
                                accept=".png, .jpg, .jpeg" />
                            {{-- <input type="hidden" name="avatar_remove_logo" /> --}}
                            {{-- <input type="file" name="userImage" id="userImage"> --}}
                        </label>

                        <span
                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                            title="Cancel avatar">
                            <i class="bi bi-x fs-2"></i>
                        </span>
                        <span
                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                            title="Remove avatar1">
                            <i class="bi bi-x fs-2" id="avatar_remove_logo"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="address" /> 
        <div class="row">
        <div class="col-md-6">

            <div class="fv-row mb-7">
                <label class="fw-bold fs-6 mb-2">Area</label>
                <input type="text" name="address_line2" id="Area" class="form-control form-control-solid mb-3 mb-lg-0"
                    placeholder="Area" value="{{ isset($default_customer_address->address_line2) ? $default_customer_address->address_line2 :  '' }}"  />
            </div>

        </div>
        <div class="col-md-6">

            <div class="fv-row mb-7">
                <label class="fw-bold fs-6 mb-2">City</label>
                <input type="text" name="city" id="city" class="form-control form-control-solid mb-3 mb-lg-0"
                    placeholder="City" value="{{ isset($default_customer_address->city) ? $default_customer_address->city :  '' }}"  />
            </div>

        </div>
        <div class="col-md-6">

            <div class="fv-row mb-7">
                <label class="fw-bold fs-6 mb-2">Pincode</label>
                <input type="text" name="post_code" id="pincode" class="form-control form-control-solid mb-3 mb-lg-0"
                    placeholder="Pincode" value="{{ isset($default_customer_address->post_code) ? $default_customer_address->post_code :  '' }}"  />
            </div>

        </div>
        <div class="col-md-6">
            <div class="fv-row mb-7">
                <label class="fw-bold fs-6 mb-2">State</label>
            <select name="state" id="state" aria-label="Select a State" class="form-select form-select-solid fw-bolder">
                <option value="">Select a State...</option>
                @foreach($state as $val)
                <option value="{{ $val->state_name }}" @if($default_customer_address && $default_customer_address->state == $val->state_name) selected @endif>
                    {{ $val->state_name }}
                </option>
            @endforeach
            </select>
            </div>
        </div>
        </div>
        <div class="fv-row mb-7">
            <label class="fw-bold fs-6 mb-2"> Status </label>
            <div class="form-check form-switch form-check-custom form-check-solid fw-bold fs-6 mb-2">
                <input class="form-check-input" type="checkbox"  name="status" value="1"  @if((isset( $info->status) && $info->status == 'published' ) || !isset($info->status)) checked @endif />
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
</div>

<style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>
<script>
    //image image script
     document.getElementById('readUrl').addEventListener('change', function() {
        // console.log("111");
        if (this.files[0]) {
            var picture = new FileReader();
            picture.readAsDataURL(this.files[0]);
            picture.addEventListener('load', function(event) {
                console.log(event.target);
                let img_url = event.target.result;
                $('#manual-image').css({
                    'background-image': 'url(' + event.target.result + ')'
                });
            });
        }
    });
    document.getElementById('avatar_remove_logo').addEventListener('click', function() {
        $('#image_remove_image').val("yes");
        $('#manual-image').css({
            'background-image': ''
        });
    });

</script>

<script>
    $('#country').select2();
    document.getElementById("dob").flatpickr({
            enableTime: false,
			dateFormat: "Y-m-d",
            maxDate: "2008-12-31"
        });
 $('.mobile_num').keypress(
        function(event) {
            if (event.keyCode == 46 || event.keyCode == 8) {
                //do nothing
            } else {
                if (event.keyCode < 48 || event.keyCode > 57) {
                    event.preventDefault();
                }
            }
        }
    );
    var add_url = "{{ route('customer.save') }}";

    // Class definition
    var KTUsersAddRole = function() {
        // Shared variables
        const element = document.getElementById('kt_common_add_form');
        const form = element.querySelector('#add_customer_form');
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
                                    message: 'First Name is required'
                                }
                            }
                        },

                        'email': {
                            validators: {
                                notEmpty: {
                                    message: 'Email is required'
                                }
                            }
                        },
                        'mobile_no': {
                            validators: {
                                notEmpty: {
                                    message: 'Mobile Number is required'
                                }
                            }
                        },
                        'customer_no': {
                            validators: {
                                notEmpty: {
                                    message: 'Customer Number is required'
                                }
                            }
                        },
                        'password': {
                            validators: {
                                notEmpty: {
                                    message: 'Password is required'
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
            $('#add_customer_form').submit(function(e) {
                // Prevent default button action
                e.preventDefault();
                // Validate form before submit
                if (validator) {
                    validator.validate().then(function(status) {
                        if (status == 'Valid') {

                            var formData = new FormData(document.getElementById(
                                "add_customer_form"));
                            submitButton.setAttribute('data-kt-indicator', 'on');
                            // Disable button to avoid multiple click
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
                                    if (res.error == 1) {
                                        // Remove loading indication
                                        submitButton.removeAttribute('data-kt-indicator');
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


</script>
