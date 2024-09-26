@extends('platform.layouts.template')
@section('toolbar')
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            @include('platform.layouts.parts._breadcrum')
        </div>
    </div>
@endsection
@section('content')
    <style>
        .paginate_button {
            padding: 5px 14px;
        }

        a.paginate_button.current {
            background: #009EF7;
            color: white;
            border-radius: 5px;
        }

        .hide-scrollbar::-webkit-scrollbar {
            width: 0 !important;
            background: transparent !important;
        }
    </style>

    <div id="kt_content_container" class="container-xxl">
        <div class="card">
            <div class="card-header border-0 pt-6 w-100">
                <div class="card-toolbar w-100">
                    <div class="d-flex justify-content-between align-items-center w-100"
                        data-kt-customer-table-toolbar="base">
                        <div>
                            <input class="p-2" type="text" id="from-date" name="to" placeholder="Start Date">
                            <input class="p-2" type="text" id="to-date" name="to" placeholder="End Date">
                        </div>
                        <div>
                            {{-- @if (access()->hasAccess('order', 'filter'))
                                @include('platform.order._filter')
                            @endif --}}
                            @include('platform.layouts.parts.common._export_button')
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body py-4">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-2 mb-0 dataTable no-footer" id="order-table">
                        <thead>
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                <th> Date </th>
                                <th> Order Id </th>
                                <th> Billing Info </th>
                                <th> Seller Info </th>
                                <th> Product Info </th>
                                <th> Payment Status </th>
                                <th> Order Status </th>
                                <th style="width: 130px;"> Actions </th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
@endsection
@section('add_on_script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>

    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    {{-- <script src="{{ asset('assets/js/datatable.min.js') }}"></script> --}}

    <script>
        var dtTable = $('#order-table').DataTable({

            processing: true,
            serverSide: true,
            type: 'POST',
            ajax: {
                "url": "{{ route('cancel-requested') }}",
                "data": function(d) {
                    d.status = $('select[name=filter_status]').val();
                    d.dateFilter = $('#date-filter').val();
                    d.fromDate = $('#from-date').val();
                    d.toDate = $('#to-date').val();
                }
            },

            columns: [{
                    data: 'created_at',
                    name: 'created_at',
                },
                {
                    data: 'order_no',
                    name: 'order_no'
                },
                {
                    data: 'billing_info',
                    name: 'billing_info',
                    bSortable: false
                },
                {
                    data: 'seller_info',
                    name: 'seller_info',
                    bSortable: false
                },
                {
                    data: 'product_name',
                    name: 'product_name',
                    bSortable: false
                },
                {
                    data: 'payment_status',
                    name: 'payment_status'
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

        function viewOrder(id) {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: "{{ route('order.view') }}",
                type: 'POST',
                data: {
                    id: id
                },
                success: function(res) {

                    //Hide scrollbar in drawer
                    $(document).ready(function() {
                        $('body').toggleClass('hide-scrollbar');
                    });

                    //Drawer data
                    $('#form-common-content').html(res);
                    const drawerEl = document.querySelector("#kt_common_add_form");
                    const commonDrawer = KTDrawer.getInstance(drawerEl);
                    commonDrawer.show();
                    return false;

                },
                error: function(xhr, err) {

                    if (xhr.status == 403) {
                        toastr.error(xhr.statusText, 'UnAuthorized Access');
                    }

                }
            });

        }

        function changeStatusModal(id) {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: "{{ route('order.cancel-status.modal') }}",
                type: 'POST',
                data: {
                    id: id
                },
                success: function(res) {

                    //Hide scrollbar in drawer
                    $(document).ready(function() {
                        $('body').toggleClass('hide-scrollbar');
                    });
                    $('#form-common-content').html(res);
                    const drawerEl = document.querySelector("#kt_common_add_form");
                    const commonDrawer = KTDrawer.getInstance(drawerEl);
                    commonDrawer.show();
                    return false;
                },
                error: function(xhr, err) {
                    if (xhr.status == 403) {
                        toastr.error(xhr.statusText, 'UnAuthorized Access');
                    }
                }
            });

        }


        //datepicker

        from = $("#from-date")
            .datepicker({
                //   defaultDate: "+1w",
                changeMonth: true,
                numberOfMonths: 1,
                dateFormat: "yy-mm-dd"
            })
            .on("change", function() {
                to.datepicker("option", "minDate", getDate(this));
                dtTable.ajax.reload();
            }),
            to = $("#to-date").datepicker({
                // defaultDate: "+1w",
                changeMonth: true,
                numberOfMonths: 1,
                dateFormat: "yy-mm-dd"
            })
            .on("change", function() {
                from.datepicker("option", "maxDate", getDate(this));
                dtTable.ajax.reload();
            });

        //Date picker function
        function getDate(element) {
            var date;
            try {
                date = $.datepicker.parseDate(dateFormat, element.value);
            } catch (error) {
                date = null;
            }

            return date;
        }
    </script>
@endsection
