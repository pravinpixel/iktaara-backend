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
                <div class="d-flex justify-content-end w-100" data-kt-merchants-table-toolbar="base">


                        @include('platform.merchants._filter_zone')


                   @include('platform.merchants._filter')
                    @include('platform.layouts.parts.common._export_button')
                    <button type="button" class="btn btn-primary" onclick="return openForm('merchants')">
                        <span class="svg-icon svg-icon-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2"
                                    rx="1" transform="rotate(-90 11.364 20.364)" fill="currentColor" />
                                <rect x="4.36396" y="11.364" width="16" height="2" rx="1"
                                    fill="currentColor" />
                            </svg>
                        </span>
                        Add Merchant
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-2 mb-0 dataTable no-footer" id="merchants-table">
                    <thead>
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th>Merchant No</th>
                            <th>Info</th>
                            {{-- <th>Email</th>
                            <th>Mobile</th> --}}
                            <th>Priority</th>
                            <th>Zone</th>
                            <th>State</th>
                            <th>Status</th>
                            <th style="width: 100px;">View Products</th>
                            <th style="width: 100px;">View Orders</th>
                            <th style="width: 75px;">Action</th>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>
    {{-- <script src="{{ asset('assets/js/datatable.min.js') }}"></script> --}}
    <script >
    var dtTable = $('#merchants-table').DataTable({

        processing: true,
        serverSide: true,
        type: 'POST',
        ajax: {
            "url": "{{ route('merchants') }}",
            "data": function(d) {
                d.status = $('select[name=filter_status]').val();
                d.zone = $('select[name=filter_zone]').val();
            }
        },

        columns: [
            {
                data: 'merchant_no',
                name: 'merchant_no'
            },
            {
                data: 'merchant_info',
                name: 'merchant_info',
                bSortable: false
            },
            // {
            //     data: 'email',
            //     name: 'email'
            // },
            // {
            //     data: 'mobile_no',
            //     name: 'mobile_no'
            // },
            {
                data: 'priority',
                name: 'priority'
            },
            {
                data: 'zone_name',
                name: 'zone'
            },
            {
                data: 'state.state_name',
                name: 'state'
            },

            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'view_products',
                name: 'view_products'
            },
            {
                data: 'view_orders',
                name: 'view_orders'
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
        $('#search-form-zone').on('submit', function(e) {
            dtTable.draw();
            e.preventDefault();
        });
        $('#search-form-zone').on('reset', function(e) {
            $('select[name=filter_zone]').val(0).change();

            dtTable.draw();
            e.preventDefault();
        });
</script>
@endsection

