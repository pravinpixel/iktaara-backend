<!--begin::Header-->
<div class="card-header" id="kt_activities_header">
    <h3 class="card-title fw-bolder text-dark">{{ $modal_title ?? 'View Review' }}</h3>
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

<div class="card-body">
    <table class="table table-responsive table-row-dashed">
        <tr>
            <td>Order No</td>
            <td>{!! $review->order_no !!}</td>
        </tr>
        <tr>
            <td>Product Name</td>
            <td>{!! $review->product_name !!}</td>
        </tr>
         <tr>
            <td>Customer email</td>
            <td>{!! $review->email !!}</td>
        </tr>
         <tr>
            <td>Customer Mobile</td>
            <td>{!! $review->mobile_no !!}</td>
        </tr>
         <tr>
            <td>Rating</td>
            <td>{!! $review->star !!}</td>
        </tr>
         <tr>
            <td>Comments</td>
            <td>{!! $review->comments !!}</td>
        </tr>
         <tr>
            <td>Status</td>
            <td>{!! ($review->status == 0) ? 'Unpublish' : 'Publish'!!}</td>
        </tr>
          <tr>
            <td>Created At</td>
            <td>{!! $review->created_at !!}</td>
        </tr>
    </table>
</div>
<div class="card-footer">
    <div>
        <button type="button" class="btn btn-sm btn-active-light-primary" id="kt_activities_close">Close</button>
    </div>
</div>
