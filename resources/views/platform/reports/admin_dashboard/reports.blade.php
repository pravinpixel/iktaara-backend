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
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <div class="m-0">
                    <a href="#" class="btn btn-sm btn-flex btn-success btn-active-primary fw-bolder"
                        onclick="return exportProductExcel()">
                        <span class="svg-icon svg-icon-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <rect opacity="0.3" x="12.75" y="4.25" width="12" height="2" rx="1"
                                    transform="rotate(90 12.75 4.25)" fill="currentColor" />
                                <path
                                    d="M12.0573 6.11875L13.5203 7.87435C13.9121 8.34457 14.6232 8.37683 15.056 7.94401C15.4457 7.5543 15.4641 6.92836 15.0979 6.51643L12.4974 3.59084C12.0996 3.14332 11.4004 3.14332 11.0026 3.59084L8.40206 6.51643C8.0359 6.92836 8.0543 7.5543 8.44401 7.94401C8.87683 8.37683 9.58785 8.34458 9.9797 7.87435L11.4427 6.11875C11.6026 5.92684 11.8974 5.92684 12.0573 6.11875Z"
                                    fill="currentColor" />
                                <path
                                    d="M18.75 8.25H17.75C17.1977 8.25 16.75 8.69772 16.75 9.25C16.75 9.80228 17.1977 10.25 17.75 10.25C18.3023 10.25 18.75 10.6977 18.75 11.25V18.25C18.75 18.8023 18.3023 19.25 17.75 19.25H5.75C5.19772 19.25 4.75 18.8023 4.75 18.25V11.25C4.75 10.6977 5.19771 10.25 5.75 10.25C6.30229 10.25 6.75 9.80228 6.75 9.25C6.75 8.69772 6.30229 8.25 5.75 8.25H4.75C3.64543 8.25 2.75 9.14543 2.75 10.25V19.25C2.75 20.3546 3.64543 21.25 4.75 21.25H18.75C19.8546 21.25 20.75 20.3546 20.75 19.25V10.25C20.75 9.14543 19.8546 8.25 18.75 8.25Z"
                                    fill="currentColor" />
                            </svg>
                        </span>
                        Download Report </a>
                </div>
                {{-- <a href="{{ $addHref }}" class="btn btn-sm btn-primary" >Add Product</a> --}}
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div id="kt_content_container" class="container-xxl">
        <div class="card">
            <div class="card-header border-0 pt-6 w-100">
                {{-- @include('platform.reports.products._inventory_filter_form') --}}
            </div>
            <hr>
            <div class="card-body py-4">
                <h1>Admin Dashboard Reports</h1>

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-2 mb-0 dataTable no-footer"
                        id="product-table">
                        <thead>
                            <th class="fw-bolder fs-7 text-uppercase" colspan="3">Sales</th>
                            <th class="fw-bolder fs-7 text-uppercase" colspan="2">Orders</th>
                            <th class="fw-bolder fs-7 text-uppercase" colspan="2">Partners</th>
                            <th class="fw-bolder fs-7 text-uppercase" colspan="2">Inventory</th>
                        </thead>
                        <tbody>
                            <tr>
                                <th class="fw-bolder fs-7" scope="col">Total Revenue</th>
                                <td colspan="2" scope="col">{{ $total_revenue }}</td>

                                <th class="fw-bolder fs-7" scope="col">Awaiting Approval</th>
                                <td scope="col">{{ $pending_orders }}</td>

                                <th class="fw-bolder fs-7" scope="col">Total Registered Vendors</th>
                                <td scope="col">{{ $total_merchants_count }}</td>

                                <th class="fw-bolder fs-7" scope="col">Total Stocks</th>
                                <td scope="col">{{ $total_stock_count }}</td>
                            </tr>
                            <tr>
                                <th class="fw-bolder fs-7" scope="col" scope="col">Average Order Value</th>
                                <td colspan="2" scope="col">{{ $average_revenue }}</td>

                                <th class="fw-bolder fs-7" scope="col" scope="col">Confirmed</th>
                                <td scope="col">{{ $confirmed_orders }}</td>

                                <th class="fw-bolder fs-7" scope="col" scope="col">Total Products</th>
                                <td scope="col">{{ $total_products }}</td>

                                <th class="fw-bolder fs-7" scope="col" scope="col">Out of stock items</th>
                                <td scope="col">{{ $out_of_stock_items_count }}</td>

                            </tr>
                            <tr>
                                <th class="fw-bolder fs-7" scope="col" scope="col">Total Orders</th>
                                <td colspan="2" scope="col">{{ $total_orders }}</td>

                                <th class="fw-bolder fs-7" scope="col" scope="col">Shipped</th>
                                <td scope="col">{{ $shipped_orders }}</td>

                                <th class="fw-bolder fs-7" scope="col" scope="col">Newly added Vendors</th>
                                <td scope="col">{{ $newly_added_merchants_count }}</td>

                                <th class="fw-bolder fs-7" scope="col" scope="col">Low Stock Product</th>
                                <td scope="col">{{ $low_stock_items_count }}</td>
                            </tr>
                            <tr>
                                <th class="fw-bolder fs-7" scope="col" scope="col">Total Quantity</th>
                                <td colspan="2" scope="col">{{ $total_quantity }}</td>

                                <th class="fw-bolder fs-7" scope="col" scope="col">Delivered</th>
                                <td scope="col">{{ $delivered_orders }}</td>

                                <th class="fw-bolder fs-7" scope="col" scope="col">Active Vendors</th>
                                <td scope="col">{{ $active_merchants }}</td>

                                <th></th>
                                <td></td>

                            </tr>
                            {{-- <tr>
                                <th scope="col">Abandoned Cart Rate</th>
                                <td scope="col">First</td>
                            </tr> --}}
                            <tr>
                                <th class="fw-bolder fs-7" scope="col" scope="col">Total Customers</th>
                                <td colspan="2" scope="col">{{ $total_customers }}</td>

                                <th class="fw-bolder fs-7" scope="col" scope="col">Exchanged</th>
                                <td scope="col">{{ $exchanged_orders }}</td>

                                <th class="fw-bolder fs-7" scope="col" scope="col">Inactive Vendors</th>
                                <td scope="col">{{ $in_active_merchants }}</td>

                                <th></th>
                                <td></td>

                            </tr>
                            <tr>
                                <th class="fw-bolder fs-7" scope="col" scope="col">Abandoned cart Rate</th>
                                <td scope="col">Count: {{ $abandoned_cart->abandoned_cart_count }}</td>
                                <td scope="col">Value: {{ $abandoned_cart->abandoned_cart_total }}</td>

                                <th class="fw-bolder fs-7" scope="col" scope="col">Cancelled</th>
                                <td scope="col">{{ $cancelled_orders }}</td>

                                <th></th>
                                <td></td>

                                <th></th>
                                <td></td>
                            </tr>
                            <tr>
                                <th></th>
                                <td colspan="2"></td>

                                <th class="fw-bolder fs-7" scope="col" scope="col">Exchanged</th>
                                <td scope="col">{{ $exchanged_orders }}</td>

                                <th></th>
                                <td></td>

                                <th></th>
                                <td></td>
                            </tr>
                            <tr>
                                <th></th>
                                <td colspan="2"></td>

                                <th class="fw-bolder fs-7" scope="col" scope="col">COD Orders /Prepaid</th>
                                <td scope="col">{{ $prepaid_orders }}</td>

                                <th></th>
                                <td></td>

                                <th></th>
                                <td></td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>

            <div class="card-body py-4">
                <h1>Geographic sales</h1>

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-2 mb-0 dataTable no-footer"
                        id="product-table">
                        <thead>
                            <th class="fw-bolder fs-7 text-uppercase">Zone</th>
                            <th class="fw-bolder fs-7 text-uppercase">Sales</th>
                        </thead>
                        <tbody>
                            @foreach ($sales_by_zone as $sale_data)
                                <tr>
                                    <td>{{ $sale_data['zone_name'] }}</td>
                                    <td>{{ $sale_data['total_count'] }}</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-body py-4">
                <h1>Sales by Category</h1>

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-2 mb-0 dataTable no-footer"
                        id="product-table">
                        <thead>
                            <th class="fw-bolder fs-7 text-uppercase">Category</th>
                            <th class="fw-bolder fs-7 text-uppercase">Sales</th>
                        </thead>
                        <tbody>
                            @foreach ($sales_by_category as $category_data)
                                <tr>
                                    <td>{{ $category_data['name'] }}</td>
                                    <td>{{ $category_data['total_count'] }}</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
