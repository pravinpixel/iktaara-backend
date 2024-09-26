<!--begin::Header-->
<div class="card-header" id="kt_activities_header">
    <h3 class="card-title fw-bolder text-dark">{{ $modal_title ?? 'Exchange Request' }}</h3>
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

    <input type="hidden" name="id" value="{{ $info->id }}" />
    <table class="table table-responsive table-row-dashed">
        <tr>
            <td>Order No</td>
            <td>{!! $info->order_no !!}</td>
        </tr>
        <tr>
            <td>Product Name</td>
            <td>{!! $info->product_name !!}</td>
        </tr>
        <tr>
            <td>Product Code</td>
            <td>{!! $info->hsn_code !!}</td>
        </tr>
        <tr>
            <td>Customer Name</td>
            <td>{!! $info->billing_name !!}</td>
        </tr>
        <tr>
            <td>Customer email</td>
            <td>{!! $info->billing_email !!}</td>
        </tr>
        <tr>
            <td>Customer Mobile</td>
            <td>{!! $info->billing_mobile_no !!}</td>
        </tr>
        @empty($seller)
        <tr></tr>
        @else
            <tr>
                <td>Seller Name</td>
                <td>{!! $seller->first_name ?? ('' . '' . $seller->last_name ?? ('' . ':' . $seller->merchant_no ?? '')) !!}</td>
            </tr>

            <tr>
                <td>Seller email</td>
                <td>{!! $seller->email ?? '' !!}</td>
            </tr>
            <tr>
                <td>Seller mobile</td>
                <td>{!! $seller->mobile_no ?? '' !!}</td>
            </tr>
        @endempty
        <tr>
            <td>Exchange reason</td>
            <td>{!! $info->reason_name !!}</td>
        </tr>
        <tr>
            <td>Comments</td>
            <td>{!! $info->reason !!}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>{!! $info->status == 0 ? 'Un Approved' : 'Approved' !!}</td>
        </tr>
        <tr>
            <td>Created At</td>
            <td>{!! $info->created_at !!}</td>
        </tr>
    </table>
    <!-- <div>
        <a href="javascript:void(0);" onclick="changeStatus(0);" class="btn btn-sm btn-danger" value="0">Dis
            Approve</a>
        <a href="javascript:void(0);" onclick="changeStatus(1);" class="btn btn-sm btn-primary"
            value="1">Approve</a>
    </div> -->

</div>

<div class="card-footer">
    <div>
        <button type="button" class="btn btn-sm btn-active-light-primary" id="kt_activities_close">Close</button>
    </div>
</div>
<!--
<script>
    KTUtil.onDOMContentLoaded(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        changeStatus(status) {
            $.ajax({
                url: "{{ route('order.exchange.status') }}",
                type: 'POST',
                data: {
                    id: id,
                    status: status
                },
                success: function(res) {
                    return false;
                },
                error: function(xhr, err) {
                    if (xhr.status == 403) {
                        toastr.error(xhr.statusText, 'UnAuthorized Access');
                    }
                }
            });
        }
    });
</script>
-->
