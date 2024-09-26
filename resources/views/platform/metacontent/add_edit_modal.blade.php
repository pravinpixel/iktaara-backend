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
<form id="add_metacontent_form" class="form" action="#" enctype="multipart/form-data">

    <div class="card-body position-relative" id="kt_activities_body">
        <div id="kt_activities_scroll" class="position-relative scroll-y me-n5 pe-5" data-kt-scroll="true"
            data-kt-scroll-height="auto" data-kt-scroll-wrappers="#kt_activities_body"
            data-kt-scroll-dependencies="#kt_activities_header, #kt_activities_metacontent" data-kt-scroll-offset="5px">
            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_update_role_scroll">
                <div class="fv-row mb-10">
                    <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_add_user_scroll"
                        data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                        data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header"
                        data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">


                        <input type="hidden" name="id" value="{{ $info->id ?? '' }}">
                        <div class="fv-row mb-7">
                            <label class="required fw-bold fs-6 mb-2">Name</label>
                            <input type="text" name="page_name" class="form-control form-control-solid mb-3 mb-lg-0"
                                placeholder="Name of Page" value="{{ $info->page_name ?? '' }}" />
                        </div>
                         <div class="fv-row mb-7">
                            <label class="required fw-bold fs-6 mb-2">Meta Title</label>
                            <input type="text" name="meta_title" class="form-control form-control-solid mb-3 mb-lg-0"
                                placeholder="Meta Title" value="{{ $info->meta_title ?? '' }}" />

                        </div>
                        <div class="fv-row mb-7">
                            <label class="fw-bold fs-6 mb-2">Meta Keywords</label>
                            <input type="text" name="meta_keywords" class="form-control form-control-solid mb-3 mb-lg-0"
                                placeholder="Meta Keywords" value="{{ $info->meta_keywords ?? '' }}" />

                        </div>
                        <div class="fv-row mb-7">
                            <label class="required fw-bold fs-6 mb-2">Meta Description</label>
                            <textarea class="form-control textarea" rows="2" name='meta_description' id="editor">
                                {{ $info->meta_description ?? '' }}
                            </textarea>


                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-metacontent py-5 text-center" id="kt_activities_metacontent">
        <div class="text-end px-8">
            <button type="reset" class="btn btn-light me-3" id="discard">Discard</button>
            <button type="submit" class="btn btn-primary" data-kt-metacontent-modal-action="submit">
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
        ClassicEditor
            .create( document.querySelector( '#editor' ) )
            .catch( error => {
                console.error( error );
            } );
    </script>
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

    var add_url = "{{ route('metacontent.save') }}";

    // Class definition
    var KTMetaContent = function() {
        // Shared variables
        const element = document.getElementById('kt_common_add_form');
        const form = element.querySelector('#add_metacontent_form');
        const modal = new bootstrap.Modal(element);

        const drawerEl = document.querySelector("#kt_common_add_form");
        const commonDrawer = KTDrawer.getInstance(drawerEl);


        // Init add schedule modal
        var initMetaContent = () => {

            // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
            var validator = FormValidation.formValidation(
                form, {
                    fields: {
                        'name': {
                            validators: {
                                notEmpty: {
                                    message: 'Content is required'
                                }
                            }
                        },
                        'slug': {
                            validators: {
                                notEmpty: {
                                    message: 'Slug is required'
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
            const submitButton = element.querySelector('[data-kt-metacontent-modal-action="submit"]');
            $('#add_metacontent_form').submit(function(e) {
                // Prevent default button action
                e.preventDefault();
                // Validate form before submit
                if (validator) {
                    validator.validate().then(function(status) {
                        if (status == 'Valid') {

                            var formData = new FormData(document.getElementById(
                                "add_metacontent_form"));
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
                initMetaContent();
            }
        };
    }();


    KTUtil.onDOMContentLoaded(function() {
        KTMetaContent.init();
    });

</script>

