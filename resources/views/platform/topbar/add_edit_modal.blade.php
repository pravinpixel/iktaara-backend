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
<!--end::Header-->
<!--begin::Body-->
<form id="add_topbar_form" class="form" action="#" enctype="multipart/form-data">

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
                        <div class="fv-row mb-7">
                            <label class="required fw-bold fs-6 mb-2">Content</label>
                            <input type="text" name="content" class="form-control form-control-solid mb-3 mb-lg-0"
                                placeholder="Content" value="{{ $info->content ?? '' }}" />
                        </div>
                         <div class="fv-row mb-7">
                            <label class="required fw-bold fs-6 mb-2">Status</label>
                            
                                <select name="enabled" id="enabled" class="form-control">
                                  <option value="1" {{$info->enabled== 1  ? 'selected' : '' }} >Active</option>
                                  <option value="0" {{$info->enabled== 0  ? 'selected' : '' }} >In Active</option>
                                </select>
                        </div>

                        <div class="row">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer py-5 text-center" id="kt_activities_footer">
        <div class="text-end px-8">
            <button type="reset" class="btn btn-light me-3" id="discard">Discard</button>
            <button type="submit" class="btn btn-primary" data-kt-topbar-modal-action="submit">
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

     //image image script
    //  document.getElementById('banner_image').addEventListener('change', function() {

    //     if (this.files[0]) {
    //         var picture = new FileReader();
    //         picture.readAsDataURL(this.files[0]);
    //         picture.addEventListener('load', function(event) {
    //             console.log(event.target);
    //             let img_url = event.target.result;
    //             $('#mobile-image').css({
    //                 'background-image': 'url(' + event.target.result + ')'
    //             });
    //         });
    //     }
    // });
    //image image script
    //  document.getElementById('readUrl').addEventListener('change', function() {

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

    // $('.mobile_num').keypress(
    //     function(event) {
    //         if (event.keyCode == 46 || event.keyCode == 8) {
    //         } else {
    //             if (event.keyCode < 48 || event.keyCode > 57) {
    //                 event.preventDefault();
    //             }
    //         }
    //     }
    // );
    // document.getElementById('avatar_remove_logo').addEventListener('click', function() {
    //     $('#image_remove_image').val("yes");
    //     $('#manual-image').css({
    //         'background-image': ''
    //     });
    // });

    // document.getElementById('mobile_remove_logo').addEventListener('click', function() {
    //     $('#image_mobile_remove').val("yes");
    //     $('#mobile-image').css({
    //         'background-image': ''
    //     });
    // });

</script>

<script>

    var add_url = "{{ route('topbars.save') }}";

    // Class definition
    var KTTopbar = function() {
        // Shared variables
        const element = document.getElementById('kt_common_add_form');
        const form = element.querySelector('#add_topbar_form');
        const modal = new bootstrap.Modal(element);

        const drawerEl = document.querySelector("#kt_common_add_form");
        const commonDrawer = KTDrawer.getInstance(drawerEl);


        // Init add schedule modal
        var initTopbar = () => {

            // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
            var validator = FormValidation.formValidation(
                form, {
                    fields: {
                        'content': {
                            validators: {
                                notEmpty: {
                                    message: 'Content is required'
                                }
                            }
                        },
                        'enabled': {
                            validators: {
                                notEmpty: {
                                    message: 'Status is required'
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
            const submitButton = element.querySelector('[data-kt-topbar-modal-action="submit"]');
            $('#add_topbar_form').submit(function(e) {
                // Prevent default button action
                e.preventDefault();
                // Validate form before submit
                if (validator) {
                    validator.validate().then(function(status) {
                        if (status == 'Valid') {

                            var formData = new FormData(document.getElementById(
                                "add_topbar_form"));
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
                                        submitButton.removeAttribute(
                                            'data-kt-indicator');
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
                initTopbar();
            }
        };
    }();


    KTUtil.onDOMContentLoaded(function() {
        KTTopbar.init();
    });

</script>
