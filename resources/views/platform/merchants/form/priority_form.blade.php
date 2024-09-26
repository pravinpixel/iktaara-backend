<style>
    .card .card-header {
        min-height: 42px;
    }
</style>
<div class="card card-flush py-4">
    <form id="add_priority_form" class="form" enctype="multipart/form-data" style="overflow-x: hidden;">

    <div class=" pt-0">
        <div class="row mb-10">

            <label for="first_name" class="col-md-2 col-form-label text-left">{{ __('Priority') }}</label>

            <div class="col-md-4">
                <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select an option" id="priority" name="priority">
                    <option value="">Choose Priority</option>
                    <option value="1" @if (isset($merchantViewData->priority) && $merchantViewData->priority === 1) selected @endif>I</option>
                    <option value="2" @if (isset($merchantViewData->priority) && $merchantViewData->priority === 2) selected @endif>II</option>
                </select>
            </div>

            @error('first_name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror

            <label for="approve" class="col-md-2 col-form-label text-left">{{ __('Approve') }}</label>

            <div class="col-md-4 form-check form-switch form-check-custom form-check-solid fw-bold">
                <input class="form-check-input" type="checkbox"  name="status" value="1"  @if(isset( $merchantViewData->status ) && $merchantViewData->status == 'approved') checked @endif />
                    {{-- <input class="form-check-input" type="checkbox"  name="status" value="1" /> --}}

            </div>

            <label for="mode" class="col-md-2 col-form-label text-left">{{ __('Mode') }}</label>

            <div class="col-md-4">
                <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select an option" id="mode" name="mode">
                    <option value="">Choose Mode</option>
                    <option value="active" @if (isset($merchantViewData->mode) && $merchantViewData->mode === 'active') selected @endif>Active</option>
                    <option value="inactive" @if (isset($merchantViewData->mode) && $merchantViewData->mode === 'in_active') selected @endif>Inactive</option>
                    <option value="holiday_mode" @if (isset($merchantViewData->mode) && $merchantViewData->mode === 'holiday_mode') selected @endif>Holiday Mode</option>

                </select>
            </div>


        </div>
    </div>

        <div class="card-footer py-5 text-center" id="kt_activities_footer">
            <div class="text-end px-8">
                <button type="reset" class="btn btn-light btn-lg me-3" id="discard">Discard</button>
                <button type="submit" class="btn btn-primary" data-kt-priority-modal-action="submit">
                    <span class="indicator-label">Submit</span>
                    <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>

    </form>
</div>

<script>

    /* Merchant URL */
    var id = "{{ $id }}";
    var base_url = "{{ route('merchants.save') }}";
    var add_url = base_url+'/'+id;

    /* DOM content loading function */

    var KTProductCategory = function() {

        const element = document.getElementById('kt_common_add_form');
        const form = element.querySelector('#add_priority_form');
        const modal = new bootstrap.Modal(element);

        const drawerEl = document.querySelector("#kt_common_add_form");
        const commonDrawer = KTDrawer.getInstance(drawerEl);
        // Init add schedule modal
        var initAddRole = () => {
            // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
            var validator = FormValidation.formValidation(
                form, {
                    fields: {
                        'priority': {
                            validators: {
                                notEmpty: {
                                    message: 'Priority is required'
                                }
                            }
                        },
                        // 'contact_person': {
                        //     validators: {
                        //         notEmpty: {
                        //             message: 'Contact person is required'
                        //         }
                        //     }
                        // },
                        // 'contact_number': {
                        //     validators: {
                        //         notEmpty: {
                        //             message: 'Contact number is required'
                        //         }
                        //     }
                        // },
                        // 'email': {
                        //     validators: {
                        //         notEmpty: {
                        //             message: 'Email is required'
                        //         },
                        //         emailAddress: {
                        //             message: 'The value is not a valid email address'
                        //         }
                        //     }
                        // },
                        // 'password': {
                        //     validators: {
                        //         notEmpty: {
                        //             message: 'Password is required'
                        //         },
                        //         stringLength: {
                        //             min: 8,
                        //             max: 20,
                        //             message: 'The password must be between 8 and 20 characters'
                        //         }
                        //     }
                        // }
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
            const submitButton = element.querySelector('[data-kt-priority-modal-action="submit"]');
            // submitButton.addEventListener('click', function(e) {
            submitButton.addEventListener('click', function (e) {
                // Prevent default button action
                e.preventDefault();
                // Validate form before submit
                if (validator) {
                    validator.validate().then(function(status) {

                        if (status == 'Valid') {
                            var from = $('#from').val();
                            var form = $('#add_priority_form')[0];
                            var formData = new FormData(form);
                            formData.append('from', 'priorityForm');
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

