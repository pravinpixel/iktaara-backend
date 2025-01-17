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
                    <div class="d-flex justify-content-end w-100" data-kt-product_attribute-table-toolbar="base">

                        {{-- @include('platform.product_category._filter') --}}
                        {{-- @include('platform.layouts.parts.common._export_button') --}}


                    </div>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body py-4">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-2 mb-0 dataTable no-footer" id="product_collection-table">
                        <thead>
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                <th> Product Name  </th>
                                <th> Brand Name  </th>
                                <th> Brand Model Code  </th>
                                <th> MRP </th>
                                <th> Available Quantity </th>
                                <th> Seller SKU </th>
                                <th> Other Details about Product </th>
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
        var dtTable = $('#product_collection-table').DataTable({

            processing: true,
            serverSide: true,
            type: 'POST',
            ajax: {
                "url": "{{ route('product-requests') }}",
                "data": function(d) {
                    d.status = $('select[name=filter_status]').val();
                }
            },
            columns: [
                {
                    data: 'product_name',
                    name: 'product_name',
                },
                {
                    data: 'brand_name',
                    name: 'brand_name'
                },
                {
                    data: 'brand_model_code',
                    name: 'brand_model_code'
                },
                {
                    data: 'mrp',
                    name: 'mrp'
                },
                {
                    data: 'available_quantity',
                    name: 'available_quantity'
                },
                {
                    data: 'seller_sku',
                    name: 'seller_sku',
                }, {
                    data: 'description',
                    name: 'description',
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
    </script>
@endsection
