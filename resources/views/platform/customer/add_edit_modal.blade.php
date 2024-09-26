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


<div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-bold mb-0 align-items-center d-flex gap-10">
                <li class="nav-item">
                    <a class="nav-link text-active-primary active" data-bs-toggle="tab" href="#customer_information">Customer Information</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-active-primary" data-bs-toggle="tab" href="#change_password">Change password</a>
                </li>
                
            </ul>
        </div>
    </div>
    <div class="card-body">
        <div id="kt_activities_body">
            <div id="kt_activities_scroll" class="position-relative scroll-y me-n5 pe-5" data-kt-scroll="true"
                            data-kt-scroll-height="auto" data-kt-scroll-wrappers="#kt_activities_body"
                            data-kt-scroll-dependencies="#kt_activities_header, #kt_activities_footer" data-kt-scroll-offset="5px"
                            style="overflow-x: hidden;">
                <div class="d-flex flex-column scroll-y me-n7 pe-7 " id="kt_modal_update_role_scroll">
                    <div class="fv-row mb-1">
                        <div class="d-flex flex-column scroll-y me-n7 pe-7 py-4" id="kt_modal_add_user_scroll"
                            data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                            data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header"
                            data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="customer_information" role="tab-panel">
                                    @include('platform.customer.form.customer_information')
                                </div>
                                <div class="tab-pane fade" id="change_password" role="tab-panel">
                                    @include('platform.customer.form.change_password')
                                </div>          
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        

