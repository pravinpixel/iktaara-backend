<div>
    <form id="add_profit_form" class="form" enctype="multipart/form-data" style="overflow-x: hidden;">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-bold mb-0 d-flex justify-content-center w-100"> <!-- Updated class here -->
                    <li class="nav-item text-center pe-2 w-50"> <!-- Updated class here -->
                        <a class="nav-link text-active-primary active" data-bs-toggle="tab" href="#brand_details">Brand</a>
                    </li>
                    <li class="nav-item text-center ps-2 w-50"> <!-- Updated class here -->
                        <a class="nav-link text-active-primary" data-bs-toggle="tab" href="#category_details">Category</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="brand_details" role="tab-panel">
                    @include('platform.merchants.form.brand_details_form')
                </div>
                <div class="tab-pane fade" id="category_details" role="tab-panel">
                    @include('platform.merchants.form.category_details_form')
                </div>

            </div>
        </div>
        <div class="card-footer py-5 text-center" id="kt_activities_footer">
            <div class="text-end px-8">
                <button type="reset" class="btn btn-light btn-lg me-3" id="discard">Discard</button>
                <button type="submit" class="btn btn-primary" data-kt-profit-modal-action="submit">
                    <span class="indicator-label">Save and Next</span>
                    <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- <script>
    // On document ready
    KTUtil.onDOMContentLoaded(function() {
        // Get the tab links for both tabs
        const brandDetailsTabLink = document.querySelector('[data-bs-toggle="tab"][href="#brand_details"]');
        const categoryDetailsTabLink = document.querySelector('[data-bs-toggle="tab"][href="#category_details"]');
        
        // Get the buttons
        const discardButton = document.querySelector('#discard');
        const submitButton = document.querySelector('[data-kt-profit-modal-action="submit"]');
        
        // Check if the "Brand Details" tab is initially active
        if (brandDetailsTabLink && brandDetailsTabLink.classList.contains('active')) {
            // Hide the buttons
            if (discardButton) {
                discardButton.style.display = 'none';
            }
            if (submitButton) {
                submitButton.style.display = 'none';
            }
        }
        
        // Add event listener to the tab links to handle changes
        if (brandDetailsTabLink) {
            brandDetailsTabLink.addEventListener('shown.bs.tab', function (event) {
                // Hide the buttons when the "Brand Details" tab is active
                if (discardButton) {
                    discardButton.style.display = 'none';
                }
                if (submitButton) {
                    submitButton.style.display = 'none';
                }
            });
        }
        
        if (categoryDetailsTabLink) {
            categoryDetailsTabLink.addEventListener('shown.bs.tab', function (event) {
                // Show the buttons when the "Category Details" tab is active
                if (discardButton) {
                    discardButton.style.display = 'block'; // or 'initial'
                }
                if (submitButton) {
                    submitButton.style.display = 'block'; // or 'initial'
                }
            });
        }
    });
</script> -->

<script>

    /* Merchant URL */
    var id = "{{ $id }}";
    var base_url = "{{ route('merchants.save') }}";
    var add_url = base_url+'/'+id;

    /* DOM content loading function */

    var KTProductCategory = function() {

        const element = document.getElementById('kt_common_add_form');
        const form = element.querySelector('#add_profit_form');
        const modal = new bootstrap.Modal(element);

        const drawerEl = document.querySelector("#kt_common_add_form");
        const commonDrawer = KTDrawer.getInstance(drawerEl);
        // Init add schedule modal
        var initAddRole = () => {
            // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
            var validator = FormValidation.formValidation(
                form, {
                    fields: {
                        // 'gst_no': {
                        //     validators: {
                        //         notEmpty: {
                        //             message: 'GST number is required'
                        //         }
                        //     }
                        // },
                        // 'pan_no': {
                        //     validators: {
                        //         notEmpty: {
                        //             message: 'Contact person is required'
                        //         }
                        //     }
                        // },

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
            const submitButton = element.querySelector('[data-kt-profit-modal-action="submit"]');
            // submitButton.addEventListener('click', function(e) {
            submitButton.addEventListener('click', function (e) {
                // Prevent default button action
                e.preventDefault();
                // Validate form before submit
                if (validator) {
                    validator.validate().then(function(status) {

                        if (status == 'Valid') {

                            var from = $('#from').val();
                            var form = $('#add_profit_form')[0];
                            var formData = new FormData(form);
                            submitButton.setAttribute('data-kt-indicator', 'on');
                            submitButton.disabled = true;

                            // var brand_margin_value = {};
                            // for (const entry of formData.entries()) {
                            //     const [key, value] = entry;
                            //     if(value !== ''){
                            //         console.log(`${key}: ${value}`);
                            //         brand_margin_value[key] = value

                            //     }
                            // }
                            // form.delete(brand_margin);
                            // form.append(brand_margin_value);
                            formData.append('from', 'profitForm');
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
                                            if (result.isConfirmed) {
                                                // commonDrawer.hide();
                                                // Remove loading indication
                                                submitButton.removeAttribute('data-kt-indicator');
                                                // Enable button
                                                submitButton.disabled = false;
                                                const nextTabLink = document.querySelector('[data-bs-toggle="tab"][href="#priority"]');
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


