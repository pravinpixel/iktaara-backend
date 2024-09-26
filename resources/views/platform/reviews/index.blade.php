@extends('platform.layouts.template')
@section('toolbar')
<div class="toolbar" id="kt_toolbar">
    <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
        @include('platform.layouts.parts._breadcrum')
    </div>
</div>
@endsection
@section('content')
    <div id="kt_content_container" class="container-xxl">
        <div class="card">
            <div class="card-header border-0 pt-6 w-100">
                <div class="card-toolbar w-100">
                    <div class="d-flex justify-content-end w-100" data-kt-product_category-table-toolbar="base">
                        @if( access()->hasAccess('product-review', 'filter') )
                            @include('platform.reviews._filter')
                        @endif

                        @include('platform.layouts.parts.common._export_button')

                    </div>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body py-4">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-2 mb-0 dataTable no-footer" id="product-reivew-table">
                        <thead>
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                <th> Order Id  </th>
                                <th> Product </th>
                                <th> Customer </th>
                                <th> Rating </th>
                                <th> Created Date </th>
                                <th> Status </th>
                                <th style="width: 75px;"> Action </th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('add_on_script')
    {{-- <script src="{{ asset('assets/js/datatable.min.js') }}"></script> --}}
    <script>
        var dtTable = $('#product-reivew-table').DataTable({
            processing: true,
            sort:true,
            serverSide: true,
            type: 'POST',
            ajax: {
                "url": "{{ route('product-review') }}",
                "data": function(d) {
                    d.status = $('select[name=filter_status]').val();
                }
            },
            columns: [
                {
                    data: 'order_no',
                    name: 'order_no',
                },
                {
                    data: 'product_name',
                    name: 'product_name'
                },
                {
                    data: 'customer_name',
                    name: 'customer_name'
                },
                {
                    data: 'rating',
                    name: 'rating'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
            language: {
                paginate: {
                    next: '<i class="fa fa-angle-right"></i>', // or '→'
                    previous: '<i class="fa fa-angle-left"></i>' // or '←'
                }
            },
            "aaSorting": [],
            "pageLength": 25,
            "pagingType" : "listbox",
        });
        $('.dataTables_wrapper').addClass('position-relative');
        $('.dataTables_info').addClass('position-absolute');
        $('.dataTables_filter label input').addClass('form-control form-control-solid w-250px ps-14');
        $('.dataTables_filter').addClass('position-absolute end-0 top-0');
        $('.dataTables_length label select').addClass('form-control form-control-solid');

        $('#search-form').on('submit', function(e) {
            dtTable.draw();
            e.preventDefault();
        });
        $('#search-form').on('reset', function(e) {
            $('select[name=filter_status]').val(0).change();
            dtTable.draw();
            e.preventDefault();
        });

        function viewReview(id) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ route('product-review.view') }}",
                type: 'POST',
                data: {id:id},
                success: function(res) {

                    //Hide scrollbar in drawer
                    $(document).ready(function() {
                        $('body').toggleClass('hide-scrollbar');
                    });

                    //Drawer data
                    $( '#form-common-content' ).html(res);
                    const drawerEl = document.querySelector("#kt_common_add_form");
                    const commonDrawer = KTDrawer.getInstance(drawerEl);
                    commonDrawer.show();
                    return false;

                }, error: function(xhr,err){

                    if( xhr.status == 403 ) {
                        toastr.error(xhr.statusText, 'UnAuthorized Access');
                    }

                }
            });

        }
    </script>
@endsection
