@extends('platform.layouts.template')
@section('toolbar')
<style>
    .content {
  padding: 10px 0;
}
</style>
<div class="toolbar" id="kt_toolbar">
    <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
        @include('platform.layouts.parts._breadcrum')
        @include('platform.layouts.parts._menu_add_button')
    </div>
</div>
@endsection
@section('content')
    <div id="kt_content_container" class="container-xxl">
        <div class="card">
            <div class="card-header border-0 pt-6 w-100">
                {{-- @if( access()->hasAccess('products', 'filter') ) --}}
                @if( access()->hasAccess('products', 'editable') )
                    @include('platform.product._filter_form')
                @endif
            </div>
            <hr>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body py-4">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-2 mb-0 dataTable no-footer" id="product-table">
                        <thead>
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                <th> Product </th>
                                <th> SKU </th>
                                <th> Category </th>
                                <th> Brand  </th>
                                <th> Price </th>
                                <th> Qty </th>
                                <th> Video Booking </th>
                                <th> Stock Status </th>
                                <th> Status </th>
                                <th style="width: 75px;">Action</th>
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
    {{-- <script src="{{ asset('assets/js/datatable.min.js') }}"></script> --}}

<style>
     .error {
      border-color:red
   }
</style>
<script>
    $(document).ready(function(){
        $(document).on('keydown', '.quantityChange', function(e) {
            var val   = this.value;
            var id      = $(this).data('id');

            if (val == "") {
                $('#quantity_'+id).addClass('error');
                return false;
            }
            else{
                $('#quantity_'+id).removeClass('error');
            }
            if(val == 0)
            {
                $('#quantity_'+id).val('');
                return false;
            }
            $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type:'POST',
                    url:"{{ route('quantityChange') }}",
                    data: {
                        id:id,
                        value:val,
                    },

                    success: function(res) {
                        if (res.status == 0) {
                            Swal.fire({
                                title: "Couldn't update!",
                                text: res.message,
                                icon: "failure",
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn btn-failure"
                                },
                                timer: 3000
                            });
                        }else{

                        }
                    },
                    error: function(xhr,err){
                        if( xhr.status == 403 ) {
                            toastr.error(xhr.statusText, 'UnAuthorized Access');
                        }
                    }
                })

        });
    })

</script>
    <script>

        $('.numberonly').keypress(function (e) {
        var charCode = (e.which) ? e.which : event.keyCode
        if (String.fromCharCode(charCode).match(/[^0-9]/g))
            return false;
    });
        var dtTable = $('#product-table').DataTable({

            processing: true,
            serverSide: true,
            type: 'POST',
            ajax: {
                "url": "{{ route('products') }}",
                "data": function(d) {
                    return $('form#search-form').serialize() + "&" + $.param(d);
                }
            },
            columns: [
                {
                    data: 'product_name',
                    name: 'product_name',

                },
                {
                    data: 'sku',
                    name: 'sku'
                },
                {
                    data: 'category',
                    name: 'category'
                },
                {
                    data: 'brand_name',
                    name: 'brand_name'
                },
                {
                    data: 'price',
                    name: 'price'
                },
                {
                    data: 'quantity',
                    name: 'quantity'
                },
                {
                    data: 'has_video_shopping',
                    name: 'has_video_shopping'
                },
                {
                    data: 'stock_status',
                    name: 'stock_status'
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
            "sPaginationType": "listbox"
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
            $('#filter_product_category').val('').trigger('change');
            $('#filter_brand').val('').trigger('change');
            $('#filter_label').val('').trigger('change');
            $('#filter_tags').val('').trigger('change');
            $('#filter_stock_status').val('').trigger('change');
            $('#filter_product_status').val('').trigger('change');
            $('#filter_product_name').val('');
            $('#filter_video_booking').val('').trigger('change');
            dtTable.draw();
            e.preventDefault();
        });

        $('.product-select2').select2();

        function exportProductExcel() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                xhrFields: {
                    responseType: 'blob',
                },
                url: "{{ route('products.export.excel') }}",
                type: 'POST',
                data: $('form#search-form').serialize(),
                success: function(result, status, xhr) {

                    var disposition = xhr.getResponseHeader('content-disposition');
                    var matches = /"([^"]*)"/.exec(disposition);
                    var filename = (matches != null && matches[1] ? matches[1] : 'products.xlsx');

                    // The actual download
                    var blob = new Blob([result], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;

                    document.body.appendChild(link);

                    link.click();
                    document.body.removeChild(link);

                }
            });

        }

        function changeStockQuantity(product_id) {
            var productpane = $('#quantity_input_'+product_id);
            var quantityEditPane = $('#quantity_edit_'+product_id);
            productpane.hide();
            quantityEditPane.css('display', 'flex');
        }

        function closeStockQuantity(product_id) {
            var productpane = $('#quantity_input_'+product_id);
            var quantityEditPane = $('#quantity_edit_'+product_id);
            productpane.show();
            quantityEditPane.css('display', 'none');
        }
    </script>
@endsection