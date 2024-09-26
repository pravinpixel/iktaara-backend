<div class="card card-flush py-4">
    <form id="add_staturatory_form" class="form" enctype="multipart/form-data" style="overflow-x: hidden;">

        <div class=" pt-0">
            <div class="row mb-10">

                <label for="first_name" class="col-md-2 col-form-label text-left">{{ __('GST No') }}<span class="required" aria-required="true"> </span></label>

                <div class="col-md-4">
                    <input id="gst_no" type="text" class="form-control form-control-solid @error('gst_no') is-invalid @enderror"
                        name="gst_no" value="{{ $merchantStaturatoryData->gst_no ?? '' }}" required autocomplete="gst_no" autofocus>
                        @error('gst_no')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                </div>

                <div class="col-md-6 text-center">
                    <div class="fv-row mb-7">
                        <label class="d-block fw-bold fs-6 mb-5">Upload GST Document</label>

                        <div class="form-text">Allowed file types: png, jpg, jpeg.</div>
                    </div>

                    <input id="gst_remove_image" type="hidden" name="gst_remove_image" value="no">
                        <div class="image-input image-input-outline manual-image" data-kt-image-input="true"
                        style="background-image: url({{ asset('userImage/no_Image.png') }})">
                        @if (isset($merchantStaturatoryData->gst_document))
                            @if (pathinfo($merchantStaturatoryData->gst_document, PATHINFO_EXTENSION) === 'pdf')
                                <div class="image-input-wrapper w-125px h-125px manual-image" id="gstImageAppear">
                                    <iframe id="gstPdfAppear" src="{{ $merchantStaturatoryData->gst_document }}" width="100%" height="100%" ></iframe>
                                </div>
                            @else
                                <div class="image-input-wrapper w-125px h-125px manual-image" id="gstImageAppear"
                                    style="background-image: url({{ $merchantStaturatoryData->gst_document }});">
                                    <iframe id="gstPdfAppear" src="" width="100%" height="100%" ></iframe>
                                </div>
                            @endif
                            <input type="hidden" name="gst_hidden_image_url" id="gst_hidden_image_url" value="{{ $merchantStaturatoryData->gst_document }}">
                        @else

                            <div class="image-input-wrapper w-125px h-125px manual-image"
                                    id="gstImageAppear">
                                <iframe id="gstPdfAppear" src="" width="100%" height="100%" ></iframe>
                            </div>
                        @endif
                        <label
                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="change" data-bs-toggle="tooltip"
                            title="Change avatar">
                            <i class="bi bi-pencil-fill fs-7"></i>
                            <input type="file" name="gst_document" id="gstImage"
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
                            <i class="bi bi-x fs-2" id="gst_remove_file"></i>
                        </span>
                        <span
                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="preview" data-bs-toggle="tooltip"
                            title="Preview avatar">
                            <i class="bi bi-eye fs-4" id="gst_preview_file"></i>
                        </span>
                    </div>
                </div>
            </div>


            <div class="row mb-10">

                <label for="first_name" class="col-md-2 col-form-label text-left">{{ __('PAN No') }}<span class="required" aria-required="true"> </span></label>

                <div class="col-md-4">
                    <input id="pan_no" type="text" class="form-control form-control-solid @error('pan_no') is-invalid @enderror"
                        name="pan_no" value="{{ $merchantStaturatoryData->pan_no ?? ''}}" required autocomplete="pan_no" autofocus>
                        @error('pan_no')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                </div>

                <div class="col-md-6 text-center">
                    <div class="fv-row mb-7">
                        <label class="d-block fw-bold fs-6 mb-5">Upload PAN Document</label>

                        <div class="form-text">Allowed file types: png, jpg,
                            jpeg.</div>
                    </div>

                    <input id="pan_remove_image" type="hidden" name="pan_remove_image" value="no">
                    <div class="image-input image-input-outline manual-image" data-kt-image-input="true"
                        style="background-image: url({{ asset('userImage/no_Image.png') }})">
                        @if (isset($merchantStaturatoryData->pan_document))
                            @if (pathinfo($merchantStaturatoryData->pan_document, PATHINFO_EXTENSION) === 'pdf')
                                <div class="image-input-wrapper w-125px h-125px manual-image" id="panImageAppear">
                                    <iframe id="panPdfAppear" src="{{ $merchantStaturatoryData->pan_document }}" width="100%" height="100%" ></iframe>
                                </div>
                            @else
                                <div class="image-input-wrapper w-125px h-125px manual-image" id="panImageAppear"
                                    style="background-image: url({{ $merchantStaturatoryData->pan_document }});">
                                    <iframe id="panPdfAppear" src="" width="100%" height="100%" ></iframe>
                                </div>
                            @endif
                            <input type="hidden" name="pan_hidden_image_url" id="pan_hidden_image_url" value="{{ $merchantStaturatoryData->pan_document }}">
                        @else

                            <div class="image-input-wrapper w-125px h-125px manual-image"
                                    id="panImageAppear">
                                <iframe id="panPdfAppear" src="" width="100%" height="100%" ></iframe>
                            </div>
                        @endif

                        <label
                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="change" data-bs-toggle="tooltip"
                            title="Change avatar">
                            <i class="bi bi-pencil-fill fs-7"></i>
                            <input type="file" name="pan_document" id="panImage"
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
                            title="Remove avatar">
                            <i class="bi bi-x fs-2" id="pan_remove_file"></i>
                        </span>
                        <span
                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="preview" data-bs-toggle="tooltip"
                            title="Preview avatar">
                            <i class="bi bi-eye fs-4" id="pan_preview_file"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="row mb-10">

                <label for="Agreement" class="col-md-2 col-form-label text-left" ></label>

                <div class="col-md-4">
                    <input type="text" class="form-control form-control-solid block-hidden"
                        name="" value="" required autocomplete="" autofocus>
                </div>

                <div class="col-md-6 text-center">
                    <div class="fv-row mb-7">
                        <label class="d-block fw-bold fs-6 mb-5">Agreement Document</label>

                        <div class="form-text">Allowed file types: png, jpg, jpeg.</div>
                    </div>

                    <input id="agree_remove_image" type="hidden" name="agree_remove_image" value="no">
                        <div class="image-input image-input-outline manual-image" data-kt-image-input="true"
                        style="background-image: url({{ asset('userImage/no_Image.png') }})">
                        @if (isset($merchantStaturatoryData->agree_document))
                            @if (pathinfo($merchantStaturatoryData->agree_document, PATHINFO_EXTENSION) === 'pdf')
                                <div class="image-input-wrapper w-125px h-125px manual-image" id="agreeImageAppear">
                                    <iframe id="agreePdfAppear" src="{{ $merchantStaturatoryData->agree_document }}" width="100%" height="100%" ></iframe>
                                </div>
                            @else
                                <div class="image-input-wrapper w-125px h-125px manual-image" id="agreeImageAppear"
                                    style="background-image: url({{ $merchantStaturatoryData->agree_document }});">
                                    <iframe id="agreePdfAppear" src="" width="100%" height="100%" ></iframe>
                                </div>
                            @endif
                            <input type="hidden" name="agree_hidden_image_url" id="agree_hidden_image_url" value="{{ $merchantStaturatoryData->agree_document }}">
                        @else

                            <div class="image-input-wrapper w-125px h-125px manual-image"
                                    id="agreeImageAppear">
                                <iframe id="agreePdfAppear" src="" width="100%" height="100%" ></iframe>
                            </div>
                        @endif
                        <label
                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="change" data-bs-toggle="tooltip"
                            title="Change avatar">
                            <i class="bi bi-pencil-fill fs-7"></i>
                            <input type="file" name="agree_document" id="agreeImage"
                                accept=".png, .jpg, .jpeg" />

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
                            title="Remove avatar">
                            <i class="bi bi-x fs-2" id="agree_remove_file"></i>
                        </span>
                        <span
                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                            data-kt-image-input-action="preview" data-bs-toggle="tooltip"
                            title="Preview avatar">
                            <i class="bi bi-eye fs-4" id="agree_preview_file"></i>
                        </span>
                   </div>
                </div>
            </div>
        </div>

        <div class="card-footer py-5 text-center" id="kt_activities_footer">
            <div class="text-end px-8">
                <button type="reset" class="btn btn-light btn-lg me-3" id="discard">Discard</button>
                <button type="submit" class="btn btn-primary" data-kt-staturatory-modal-action="submit">
                    <span class="indicator-label">Save and next</span>
                    <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </form>
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewModalLabel">Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" style="height: 75vh;">
                <img id="ImagePreview" src="" alt="Image Preview" class="img-fluid" style="max-height:70vh">
                <iframe id="PdfPreview" src="" width="100%" height="100%"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</div>
<script>

    /* Image shown script */

    document.getElementById('gstImage').addEventListener('change', function() {
        const showPdf = document.getElementById('gstPdfAppear');
        // console.log("111");
        if (this.files[0]) {
            const file = this.files[0];
            const fileType = file.type;
            if( fileType === "application/pdf" ){
                $('#gstImageAppear').css({
                    'background-image': ''
                });
                showPdf.src = URL.createObjectURL(file);
                $('#gst_remove_image').val("no");

            }else{
                if(showPdf){
                    showPdf.src = '';
                }
                var picture = new FileReader();
                picture.readAsDataURL(this.files[0]);
                picture.addEventListener('load', function(event) {
                    let img_url = event.target.result;
                    $('#gstImageAppear').css({
                        'background-image': 'url(' + event.target.result + ')'
                    });
                    $('#gst_remove_image').val("no");
                });
            }
        }
        document.getElementById('gstImage').value = '';

    });

    document.getElementById('panImage').addEventListener('change', function() {
        const showPdf = document.getElementById('panPdfAppear');

        // console.log("111");
        if (this.files[0]) {
            const file = this.files[0];
            const fileType = file.type;
            if( fileType === "application/pdf" ){
                $('#panImageAppear').css({
                    'background-image': ''
                });
                showPdf.src = URL.createObjectURL(file);
                $('#pan_remove_image').val("no");

            }else{
                if(showPdf){
                    showPdf.src = '';
                }
                var picture = new FileReader();
                picture.readAsDataURL(this.files[0]);
                picture.addEventListener('load', function(event) {
                    let img_url = event.target.result;
                    $('#panImageAppear').css({
                        'background-image': 'url(' + event.target.result + ')'
                    });
                    $('#pan_remove_image').val("no");
                });
            }

        }
        document.getElementById('panImage').value = '';

    });

    document.getElementById('agreeImage').addEventListener('change', function() {
        const showPdf = document.getElementById('agreePdfAppear');

        // console.log("111");
        if (this.files[0]) {
            const file = this.files[0];
            const fileType = file.type;
            if( fileType === "application/pdf" ){
                $('#agreeImageAppear').css({
                    'background-image': ''
                });
                showPdf.src = URL.createObjectURL(file);
                $('#agree_remove_image').val("no");

            }else{
                if(showPdf){
                    showPdf.src = '';
                }
                var picture = new FileReader();
                picture.readAsDataURL(this.files[0]);
                picture.addEventListener('load', function(event) {
                    let img_url = event.target.result;
                    $('#agreeImageAppear').css({
                        'background-image': 'url(' + event.target.result + ')'
                    });
                    $('#agree_remove_image').val("no");
                });
            }

        }
        document.getElementById('agreeImage').value = '';

    });

    /* Image remove script */
    document.getElementById('gst_remove_file').addEventListener('click', function() {
        $('#gst_remove_image').val("yes");
        $('#gstImageAppear').css({
            'background-image': ''
        });
        $('#gstPdfAppear').attr('src', '');

    });


    document.getElementById('pan_remove_file').addEventListener('click', function() {
        $('#pan_remove_image').val("yes");
        $('#panImageAppear').css({
            'background-image': ''
        });
        $('#panPdfAppear').attr('src', '');
    });

    document.getElementById('agree_remove_file').addEventListener('click', function() {
        $('#agree_remove_image').val("yes");
        $('#agreeImageAppear').css({
            'background-image': ''
        });
        $('#agreePdfAppear').attr('src', '');

    });

    // Agree Preview file

    document.getElementById('agree_preview_file').addEventListener('click', function() {

        const imagePreviewModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));

        //If the image and pdf is url
        const agreePdfAppear = document.getElementById('agreePdfAppear');
        const agreeImageAppear = document.getElementById('agreeImageAppear');
        const agreePdfPreview =  document.getElementById('agreePdfPreview');
        const computedStyle = window.getComputedStyle(agreeImageAppear);

        // Extract the background-image property
        const backgroundImage = computedStyle.getPropertyValue('background-image');

        // Clean up the extracted URL (remove quotes and spaces)
        const imageUrl = backgroundImage.replace(/url\(['"]?(.*?)['"]?\)/i, '$1');

        //If the image is file
        const fileInput = document.getElementById('agreeImage');
        const agreeImagePreview = document.getElementById('agreeImagePreview');

        const pdfUrl = agreePdfAppear.src.split('/')

        if(imageUrl === 'none' && pdfUrl[3] === 'merchants'){
            imagePreviewModal.hide();

        }else{

            if (fileInput.files[0]) {
                if(fileInput.files[0].type === "application/pdf"){

                    $('#ImagePreview').css({
                        'display': 'none'
                    });
                    $('#PdfPreview').css({
                        'display': ''
                    });
                    PdfPreview.src = URL.createObjectURL(fileInput.files[0]);
                }else{
                    $('#PdfPreview').css({
                        'display': 'none'
                    });
                    $('#ImagePreview').css({
                        'display': ''
                    });
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        agreeImagePreview.src = event.target.result;
                    };
                    reader.readAsDataURL(fileInput.files[0]);
                }

            }else{

                if(imageUrl != 'none'){
                    $('#PdfPreview').css({
                        'display': 'none'
                    });
                    $('#ImagePreview').css({
                        'display': ''
                    });
                    ImagePreview.src = imageUrl;
                }else{
                    $('#ImagePreview').css({
                        'display': 'none'
                    });
                    $('#PdfPreview').css({
                        'display': ''
                    });
                    PdfPreview.src = agreePdfAppear.src;
                }
            }

            // Show the modal
            imagePreviewModal.show();
        }

    });

    // Pan Preview file

    document.getElementById('pan_preview_file').addEventListener('click', function() {

        const imagePreviewModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));

        //If the image and pdf is url
        const panPdfAppear = document.getElementById('panPdfAppear');
        const panImageAppear = document.getElementById('panImageAppear');
        const panPdfPreview =  document.getElementById('panPdfPreview');
        const computedStyle = window.getComputedStyle(panImageAppear);

        // Extract the background-image property
        const backgroundImage = computedStyle.getPropertyValue('background-image');

        // Clean up the extracted URL (remove quotes and spaces)
        const imageUrl = backgroundImage.replace(/url\(['"]?(.*?)['"]?\)/i, '$1');
        //If the image is file
        const fileInput = document.getElementById('panImage');
        const panImagePreview = document.getElementById('panImagePreview');

        const pdfUrl = panPdfAppear.src.split('/')

        if(imageUrl === 'none' && pdfUrl[3] === 'merchants'){
            imagePreviewModal.hide();

        }else{

            if(fileInput){
                preview(fileInput, imageUrl)
            }
            // Show the modal
            imagePreviewModal.show();
        }

    });

    // Gst Preview file

    document.getElementById('gst_preview_file').addEventListener('click', function() {

        const imagePreviewModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));

        //If the image and pdf is url
        const gstPdfAppear = document.getElementById('gstPdfAppear');
        const gstImageAppear = document.getElementById('gstImageAppear');
        const gstPdfPreview =  document.getElementById('gstPdfPreview');
        const computedStyle = window.getComputedStyle(gstImageAppear);

        // Extract the background-image property
        const backgroundImage = computedStyle.getPropertyValue('background-image');

        // Clean up the extracted URL (remove quotes and spaces)
        const imageUrl = backgroundImage.replace(/url\(['"]?(.*?)['"]?\)/i, '$1');

        //If the image is file
        const fileInput = document.getElementById('gstImage');
        const gstImagePreview = document.getElementById('gstImagePreview');

        const pdfUrl = gstPdfAppear.src.split('/')

        if(imageUrl === 'none' && pdfUrl[3] === 'merchants'){
            imagePreviewModal.hide();

        }else{

            if(fileInput){
                preview(fileInput, imageUrl)
            }
            // Show the modal
            imagePreviewModal.show();
        }

    });


    function preview(fileInput, imageUrl){
        if (fileInput.files[0]) {
            if(fileInput.files[0].type === "application/pdf"){

                $('#ImagePreview').css({
                    'display': 'none'
                });
                $('#PdfPreview').css({
                    'display': ''
                });
                PdfPreview.src = URL.createObjectURL(fileInput.files[0]);
            }else{
                $('#PdfPreview').css({
                    'display': 'none'
                });
                $('#ImagePreview').css({
                    'display': ''
                });
                var reader = new FileReader();
                reader.onload = function(event) {
                    ImagePreview.src = event.target.result;
                };
                reader.readAsDataURL(fileInput.files[0]);
            }

        }else{

            if(imageUrl != 'none'){
                $('#PdfPreview').css({
                    'display': 'none'
                });
                $('#ImagePreview').css({
                    'display': ''
                });
                ImagePreview.src = imageUrl;
            }else{
                $('#ImagePreview').css({
                    'display': 'none'
                });
                $('#PdfPreview').css({
                    'display': ''
                });
                PdfPreview.src = panPdfAppear.src;
            }
        }
    }


    /* Merchant URL */
    var id = "{{ $id }}";
    var base_url = "{{ route('merchants.save') }}";
    var add_url = base_url+'/'+id;

    /* DOM content loading function */

    var KTProductCategory = function() {

        const element = document.getElementById('kt_common_add_form');
        const form = element.querySelector('#add_staturatory_form');
        const modal = new bootstrap.Modal(element);

        const drawerEl = document.querySelector("#kt_common_add_form");
        const commonDrawer = KTDrawer.getInstance(drawerEl);
        // Init add schedule modal
        var initAddRole = () => {
            // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
            var validator = FormValidation.formValidation(
                form, {
                    fields: {
                        'gst_no': {
                            validators: {
                                notEmpty: {
                                    message: 'GST number is required'
                                }
                            }
                        },
                        'pan_no': {
                            validators: {
                                notEmpty: {
                                    message: 'PAN number is required'
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
            const submitButton = element.querySelector('[data-kt-staturatory-modal-action="submit"]');
            // submitButton.addEventListener('click', function(e) {
            submitButton.addEventListener('click', function (e) {
                // Prevent default button action
                e.preventDefault();
                // Validate form before submit
                if (validator) {
                    validator.validate().then(function(status) {

                        if (status == 'Valid') {
                            var from = $('#from').val();
                            var form = $('#add_staturatory_form')[0];
                            var formData = new FormData(form);
                            if(formData.get('gst_remove_image') === 'yes'){
                                formData.set('gst_document', null);
                            }
                            if(formData.get('pan_remove_image') === 'yes'){
                                formData.set('pan_document', null);
                            }
                            if(formData.get('agree_remove_image') === 'yes'){
                                formData.set('agree_document', null);
                            }
                            formData.append('from', 'staturatoryForm');
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
                                            if (result.isConfirmed) {
                                                // commonDrawer.hide();
                                                // Remove loading indication
                                                submitButton.removeAttribute('data-kt-indicator');
                                                // Enable button
                                                submitButton.disabled = false;
                                                const nextTabLink = document.querySelector('[data-bs-toggle="tab"][href="#profit_margin"]');
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
