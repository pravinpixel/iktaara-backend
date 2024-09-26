<?php

use App\Http\Controllers\MerchantController;
// use App\Http\Controllers\MerchantOrderController;
use App\Http\Controllers\RazorpayPaymentController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


// Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
Route::get('/test', [App\Http\Controllers\TestController::class, 'index']);
Route::get('/test-mail', [App\Http\Controllers\TestController::class, 'sendMail']);
Route::get('/test-invoice', [App\Http\Controllers\TestController::class, 'invoiceSample']);
Route::get('/generate-sitemap', [App\Http\Controllers\TestController::class, 'generateSiteMap']);

Route::get('/upload-image', [App\Http\Controllers\ImageUploadController::class, 'index']);

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::post('/dynamicview', [App\Http\Controllers\HomeController::class, 'dynamicView'])->name('home.view');

    Route::prefix('global')->group(function () {
        Route::get('/', [App\Http\Controllers\GlobalSettingController::class, 'index'])->name('global')->middleware(['checkAccess:visible']);
        Route::post('/save', [App\Http\Controllers\GlobalSettingController::class, 'saveForm'])->name('global.save')->middleware(['checkAccess:editable']);
        Route::post('/save/link', [App\Http\Controllers\GlobalSettingController::class, 'saveLinkForm'])->name('global.link.save')->middleware(['checkAccess:editable']);
        Route::post('/getTab', [App\Http\Controllers\GlobalSettingController::class, 'getTab'])->name('global.tab');
    });

    Route::prefix('my-profile')->group(function () {
        Route::get('/', [App\Http\Controllers\MyProfileController::class, 'index'])->name('my-profile')->middleware(['checkAccess:visible']);
        Route::get('/password', [App\Http\Controllers\MyProfileController::class, 'getPasswordTab'])->name('my-profile.password')->middleware(['checkAccess:editable']);
        Route::post('/getTab', [App\Http\Controllers\MyProfileController::class, 'getTab'])->name('my-profile.get.tab');
        Route::post('/save', [App\Http\Controllers\MyProfileController::class, 'saveForm'])->name('my-profile.save')->middleware(['checkAccess:editable']);
    });

    $categoriesArray = array('sub_category', 'product-tags', 'product-labels');
    foreach ($categoriesArray as $catUrl) {
        Route::prefix($catUrl)->group(function () use ($catUrl) {


            Route::get('/', [App\Http\Controllers\Category\SubCategoryController::class, 'index'])->name($catUrl)->middleware(['checkAccess:visible']);
            Route::post('/addOrEdit', [App\Http\Controllers\Category\SubCategoryController::class, 'modalAddEdit'])->name($catUrl . '.add.edit')->middleware(['checkAccess:editable']);
            Route::post('/status', [App\Http\Controllers\Category\SubCategoryController::class, 'changeStatus'])->name($catUrl . '.status')->middleware(['checkAccess:status']);
            Route::post('/delete', [App\Http\Controllers\Category\SubCategoryController::class, 'delete'])->name($catUrl . '.delete')->middleware(['checkAccess:delete']);
            Route::post('/save', [App\Http\Controllers\Category\SubCategoryController::class, 'saveForm'])->name($catUrl . '.save');
            Route::post('/export/excel', [App\Http\Controllers\Category\SubCategoryController::class, 'export'])->name($catUrl . '.export.excel')->middleware(['checkAccess:export']);
            Route::get('/export/pdf', [App\Http\Controllers\Category\SubCategoryController::class, 'exportPdf'])->name($catUrl . '.export.pdf')->middleware(['checkAccess:export']);
        });
    }

    /***** loop for same routes */
    $routeArray = array(
        'topbars' => App\Http\Controllers\TopbarController::class,
        'merchants' => App\Http\Controllers\MerchantController::class,
        'footers' => App\Http\Controllers\FooterController::class,
        'metacontent' => App\Http\Controllers\MetaContentController::class,
        'customer-requests' => App\Http\Controllers\CustomerRequestController::class,
        'brands' => App\Http\Controllers\Master\BrandController::class,
        'product-category' => App\Http\Controllers\Product\ProductCategoryController::class,
        'tax' => App\Http\Controllers\Settings\TaxController::class,
        'charges' => App\Http\Controllers\ChargesController::class,
        'coupon' => App\Http\Controllers\Offers\CouponController::class,
        'discount' => App\Http\Controllers\Offers\DiscountController::class,
        'email-template' => App\Http\Controllers\Master\EmailTemplateController::class,
        'video-booking' => App\Http\Controllers\VideoBookingController::class,
        'walkthroughs' => App\Http\Controllers\WalkThroughController::class,
        'testimonials' => App\Http\Controllers\TestimonialsController::class,
        'main_category' => App\Http\Controllers\Category\MainCategoryController::class,
        'pincode' => App\Http\Controllers\Master\PincodeController::class,
        'city' => App\Http\Controllers\Master\CityController::class,
        'state' => App\Http\Controllers\Master\StateController::class,
        'country' => App\Http\Controllers\Master\CountryController::class,
        'order-status' => App\Http\Controllers\Master\OrderStatusController::class,
        'users' => App\Http\Controllers\UserController::class,
        'sms-template' => App\Http\Controllers\SmsTemplateController::class,
        'payment-gateway' => App\Http\Controllers\PaymentGatewayController::class,
        'roles' => App\Http\Controllers\Settings\RoleController::class,
        'customer' => App\Http\Controllers\CustomerController::class,
        'banner' => App\Http\Controllers\BannerController::class,
        'newsletter' => App\Http\Controllers\NewsletterController::class,
        'quick-link'    => App\Http\Controllers\Master\QuickLinkController::class,
        'order-cancel'    => App\Http\Controllers\OrderCancelController::class,
        'order-reject'    => App\Http\Controllers\OrderRejectController::class,
        'exchange-status' => App\Http\Controllers\ExchangeStatusController::class,
    );

    Route::get('/exchange-status', [App\Http\Controllers\ExchangeStatusController::class, 'index'])->name('exchange-status')->middleware(['checkAccess:visible']);
    Route::post('/exchange-status/create', [App\Http\Controllers\ExchangeStatusController::class, 'modalAddEdit'])->name('exchange-status.create')->middleware(['checkAccess:visible']);
    Route::post('/exchange-status/save', [App\Http\Controllers\ExchangeStatusController::class, 'saveForm'])->name('exchange-status.save')->middleware(['checkAccess:visible']);
    Route::Post('/exchange-status/view', [App\Http\Controllers\ExchangeStatusController::class, 'view'])->name('exchange-status.view')->middleware(['checkAccess:visible']);
    Route::post('/exchange-status/status', [App\Http\Controllers\ExchangeStatusController::class, 'changeStatus'])->name('exchange-status.status')->middleware(['checkAccess:status']);
    Route::post('/exchange-status/delete', [App\Http\Controllers\ExchangeStatusController::class, 'delete'])->name('exchange-status.delete')->middleware(['checkAccess:delete']);
    Route::post('/exchange-status/export/excel', [App\Http\Controllers\ExchangeStatusController::class, 'export'])->name('exchange-status.export.excel')->middleware(['checkAccess:export']);


    foreach ($routeArray as $key => $value) {
        Route::prefix($key)->group(function () use ($key, $value) {
            Route::get('/', [$value, 'index'])->name($key)->middleware(['checkAccess:visible']);
            Route::post('/addOrEdit', [$value, 'modalAddEdit'])->name($key . '.add.edit')->middleware(['checkAccess:editable']);
            Route::post('/status', [$value, 'changeStatus'])->name($key . '.status')->middleware(['checkAccess:status']);
            if ($key == 'customer') {
                Route::post('/delete', [$value, 'delete'])->name($key . '.delete1');
            }else
           Route::post('/delete', [$value, 'delete'])->name($key . '.delete')->middleware(['checkAccess:delete']);

            if ($key == 'merchants') {
                Route::post('/save/{id?}', [$value, 'dataSaveEditForm'])->name($key . '.save');
            }
            else Route::post('/save', [$value, 'saveForm'])->name($key . '.save');
            Route::post('/export/excel', [$value, 'export'])->name($key . '.export.excel')->middleware(['checkAccess:export']);
            Route::get('/export/pdf', [$value, 'exportPdf'])->name($key . '.export.pdf')->middleware(['checkAccess:export']);
        });
    }

    Route::prefix('coupon')->group(function () {
        Route::get('/coupon-gendrate', [App\Http\Controllers\Offers\CouponController::class, 'couponGendrate'])->name('coupon.coupon-gendrate');
        Route::post('/coupon-apply', [App\Http\Controllers\Offers\CouponController::class, 'couponType'])->name('coupon.coupon-apply');
    });
    Route::post('discount/get/discount-type/data', [App\Http\Controllers\Offers\DiscountController::class, 'getDiscountTypeData'])->name('discount.coupon-apply');

    Route::prefix('products')->group(function () {
        Route::get('/', [App\Http\Controllers\Product\ProductController::class, 'index'])->name('products')->middleware(['checkAccess:visible']);
        Route::get('/upload', [App\Http\Controllers\Product\ProductController::class, 'bulkUpload'])->name('products.upload')->middleware(['checkAccess:editable']);
        Route::post('/upload/product', [App\Http\Controllers\Product\ProductController::class, 'doBulkUpload'])->name('products.bulk.upload')->middleware(['checkAccess:editable']);
        Route::post('/upload/attributes/product', [App\Http\Controllers\Product\ProductController::class, 'doAttributesBulkUpload'])->name('products.bulk.attributes.upload')->middleware(['checkAccess:editable']);
        Route::get('/add/{id?}', [App\Http\Controllers\Product\ProductController::class, 'addEditPage'])->name('products.add.edit')->middleware(['checkAccess:editable']);
        Route::post('/status', [App\Http\Controllers\Product\ProductController::class, 'changeStatus'])->name('products.status')->middleware(['checkAccess:status']);
        Route::post('/delete', [App\Http\Controllers\Product\ProductController::class, 'delete'])->name('products.delete')->middleware(['checkAccess:delete']);
        Route::post('/save', [App\Http\Controllers\Product\ProductController::class, 'saveForm'])->name('products.save');
        Route::post('/get/base/mrp', [App\Http\Controllers\Product\ProductController::class, 'getBaseMrpPrice'])->name('get.product.base_mrp_prce');
        Route::post('/get/category/info', [App\Http\Controllers\Product\ProductController::class, 'getCategoryInfoTax'])->name('get.product.taxCategory');
        Route::post('/remove/image', [App\Http\Controllers\Product\ProductController::class, 'removeImage'])->name('products.remove.image');
        Route::post('/remove/images', [App\Http\Controllers\Product\ProductController::class, 'removeImages'])->name('products.remove.images');
        Route::post('/setOrder/image', [App\Http\Controllers\Product\ProductController::class, 'setImageOrder'])->name('products.image.order');
        Route::post('/remove/brochure', [App\Http\Controllers\Product\ProductController::class, 'removeBrochure'])->name('products.remove.brochure');
        Route::post('/upload/brochure', [App\Http\Controllers\Product\ProductController::class, 'uploadBrochure'])->name('products.upload.brochure');
        Route::post('/upload/gallery', [App\Http\Controllers\Product\ProductController::class, 'uploadGallery'])->name('products.upload.gallery');
        Route::post('/export/excel', [App\Http\Controllers\Product\ProductController::class, 'export'])->name('products.export.excel')->middleware(['checkAccess:export']);
        Route::get('/export/pdf', [App\Http\Controllers\Product\ProductController::class, 'exportPdf'])->name('products.export.pdf')->middleware(['checkAccess:export']);

        Route::post('/attribute/row', [App\Http\Controllers\Product\ProductAttributeSetController::class, 'getAttributeRow'])->name('products.attribute.row');
        /***** Attribute set values */
        Route::get('/attribute', [App\Http\Controllers\Product\ProductAttributeSetController::class, 'index'])->name('product-attribute')->middleware(['checkAccess:visible']);
        Route::post('/attribute/addOrEdit', [App\Http\Controllers\Product\ProductAttributeSetController::class, 'modalAddEdit'])->name('product-attribute.add.edit')->middleware(['checkAccess:editable']);
        Route::post('/attribute/status', [App\Http\Controllers\Product\ProductAttributeSetController::class, 'changeStatus'])->name('product-attribute.status')->middleware(['checkAccess:status']);
        Route::post('/attribute/delete', [App\Http\Controllers\Product\ProductAttributeSetController::class, 'delete'])->name('product-attribute.delete')->middleware(['checkAccess:delete']);
        Route::post('/attribute/save', [App\Http\Controllers\Product\ProductAttributeSetController::class, 'saveForm'])->name('product-attribute.save')->middleware(['checkAccess:editable']);
        Route::post('/attribute/export/excel', [App\Http\Controllers\Product\ProductAttributeSetController::class, 'export'])->name('product-attribute.export.excel')->middleware(['checkAccess:export']);
        Route::get('/attribute/export/pdf', [App\Http\Controllers\Product\ProductAttributeSetController::class, 'exportPdf'])->name('product-attribute.export.pdf')->middleware(['checkAccess:export']);
        /****** Product Collection */
        Route::get('/collection', [App\Http\Controllers\Product\ProductCollectionController::class, 'index'])->name('product-collection')->middleware(['checkAccess:visible']);
        Route::post('/collection/addOrEdit', [App\Http\Controllers\Product\ProductCollectionController::class, 'modalAddEdit'])->name('product-collection.add.edit')->middleware(['checkAccess:editable']);
        Route::post('/collection/status', [App\Http\Controllers\Product\ProductCollectionController::class, 'changeStatus'])->name('product-collection.status')->middleware(['checkAccess:status']);
        Route::post('/collection/delete', [App\Http\Controllers\Product\ProductCollectionController::class, 'delete'])->name('product-collection.delete')->middleware(['checkAccess:delete']);
        Route::post('/collection/save', [App\Http\Controllers\Product\ProductCollectionController::class, 'saveForm'])->name('product-collection.save')->middleware(['checkAccess:editable']);
        Route::post('/collection/export/excel', [App\Http\Controllers\Product\ProductCollectionController::class, 'export'])->name('product-collection.export.excel')->middleware(['checkAccess:export']);
        Route::get('/collection/export/pdf', [App\Http\Controllers\Product\ProductCollectionController::class, 'exportPdf'])->name('product-collection.export.pdf')->middleware(['checkAccess:export']);


        Route::get('/combos', [App\Http\Controllers\Product\ComboProductController::class, 'index'])->name('combo-product')->middleware(['checkAccess:visible']);
        Route::post('/combos/addOrEdit', [App\Http\Controllers\Product\ComboProductController::class, 'modalAddEdit'])->name('combo-product.add.edit')->middleware(['checkAccess:editable']);
        Route::post('/combos/status', [App\Http\Controllers\Product\ComboProductController::class, 'changeStatus'])->name('combo-product.status')->middleware(['checkAccess:status']);
        Route::post('/combos/delete', [App\Http\Controllers\Product\ComboProductController::class, 'delete'])->name('combo-product.delete')->middleware(['checkAccess:delete']);
        Route::post('/combos/save', [App\Http\Controllers\Product\ComboProductController::class, 'saveForm'])->name('combo-product.save')->middleware(['checkAccess:editable']);
        Route::post('/combos/export/excel', [App\Http\Controllers\Product\ComboProductController::class, 'export'])->name('combo-product.export.excel')->middleware(['checkAccess:export']);
        Route::get('/combos/export/pdf', [App\Http\Controllers\Product\ComboProductController::class, 'exportPdf'])->name('combo-product.export.pdf')->middleware(['checkAccess:export']);

        Route::post('/quantityChange', [App\Http\Controllers\Product\ProductController::class, 'quantityChange'])->name('quantityChange');

        Route::get('/reviews', [App\Http\Controllers\ReivewsController::class, 'index'])->name('product-review')->middleware(['checkAccess:visible']);
        Route::Post('/reviews/view', [App\Http\Controllers\ReivewsController::class, 'view'])->name('product-review.view')->middleware(['checkAccess:visible']);
        Route::post('/reviews/status', [App\Http\Controllers\ReivewsController::class, 'changeStatus'])->name('product-review.status')->middleware(['checkAccess:status']);
        Route::post('/reviews/delete', [App\Http\Controllers\ReivewsController::class, 'delete'])->name('product-review.delete')->middleware(['checkAccess:delete']);
        Route::post('/reviews/export/excel', [App\Http\Controllers\ReivewsController::class, 'export'])->name('product-review.export.excel')->middleware(['checkAccess:export']);
    });

    Route::post('/getProduct/category/list', [App\Http\Controllers\CommonController::class, 'getProductCategoryList'])->name('common.category.dropdown');
    Route::post('/getProduct/brand/list', [App\Http\Controllers\CommonController::class, 'getProductBrandList'])->name('common.brand.dropdown');
    Route::post('/getProduct/dynamic/list', [App\Http\Controllers\CommonController::class, 'getProductDynamicList'])->name('common.dynamic.dropdown');
    Route::get('/filter', [App\Http\Controllers\CommonController::class, 'filterAddressDropdown'])->name('filterAddress');

    Route::prefix('customer')->group(function () {

        Route::get('/coupon-gendrate', [App\Http\Controllers\CustomerController::class, 'couponGendrate'])->name('customer.coupon-gendrate');
        Route::post('/coupon-apply', [App\Http\Controllers\CustomerController::class, 'couponType'])->name('customer.coupon-apply');
        Route::post('/address', [App\Http\Controllers\CustomerController::class, 'customerAddress'])->name('customer.address');
        Route::post('/change-password', [App\Http\Controllers\CustomerController::class, 'customerChangePassword'])->name('customer.change.password');

        Route::post('/customer/delete', [App\Http\Controllers\CustomerController::class, 'customerDelete'])->name('customer.delete')->middleware(['checkAccess:delete']);
        Route::get('/customer/view/{id}', [App\Http\Controllers\CustomerController::class, 'view'])->name('customer.view')->middleware(['checkAccess:visible']);
        Route::get('/add-address', [App\Http\Controllers\CustomerController::class, 'addAddress'])->name('customer.add-address')->middleware(['checkAccess:editable']);
        Route::post('/address/list', [App\Http\Controllers\CustomerController::class, 'addressList'])->name('customer.address.list')->middleware(['checkAccess:visible']);
        Route::post('/address/delete', [App\Http\Controllers\CustomerController::class, 'addressDelete'])->name('customer.address.delete')->middleware(['checkAccess:delete']);

    });

    Route::prefix('order')->group(function () {
        Route::get('/', [App\Http\Controllers\OrderController::class, 'index'])->name('order');
        Route::get('/cancel-requested', [App\Http\Controllers\OrderController::class, 'cancelRequested'])->name('cancel-requested');
        Route::post('/view', [App\Http\Controllers\OrderController::class, 'orderView'])->name('order.view');
        Route::post('/order/cancel/view', [App\Http\Controllers\OrderController::class, 'cancelView'])->name('order.cancel.view');
        Route::post('/open/orderStatus/modal', [App\Http\Controllers\OrderController::class, 'openOrderStatusModal'])->name('order.status.modal');
        Route::post('/open/cancel-reqeuest-status/modal', [App\Http\Controllers\OrderController::class, 'cancelRequestStatusModal'])->name('order.cancel-status.modal');
        Route::post('/change/order/status', [App\Http\Controllers\OrderController::class, 'changeOrderStatus'])->name('order.change.status');
        Route::post('/change/order/update-cancel-request-status', [App\Http\Controllers\OrderController::class, 'changeCancelRequestStatus'])->name('order.cancel-request.status');
        Route::post('/export/excel', [App\Http\Controllers\OrderController::class, 'export'])->name('order.export.excel')->middleware(['checkAccess:export']);

        Route::get('/exchange', [App\Http\Controllers\OrderExchangeController::class, 'index'])->name('exchange-requested')->middleware(['checkAccess:visible']);
        Route::Post('/exchange/view', [App\Http\Controllers\OrderExchangeController::class, 'view'])->name('order.exchange.view')->middleware(['checkAccess:visible']);
        Route::post('/exchange/status', [App\Http\Controllers\OrderExchangeController::class, 'changeStatus'])->name('order.exchange.status')->middleware(['checkAccess:status']);
        Route::post('/exchange/status/update', [App\Http\Controllers\OrderExchangeController::class, 'updateStatus'])->name('order.exchange.status-update')->middleware(['checkAccess:status']);
        Route::post('/exchange/delete', [App\Http\Controllers\OrderExchangeController::class, 'delete'])->name('order.exchange.delete')->middleware(['checkAccess:delete']);
        Route::post('/exchange/export/excel', [App\Http\Controllers\OrderExchangeController::class, 'export'])->name('order.exchange.export.excel')->middleware(['checkAccess:export']);
    });

    Route::prefix('reports')->middleware(['checkAccess:visible'])->group(function () {
        Route::get('/sale', [App\Http\Controllers\ReportProductController::class, 'index'])->name('reports.sale');
        Route::get('/orders', [App\Http\Controllers\ReportProductController::class, 'ordersReport'])->name('reports.order');
        Route::get('/seller', [App\Http\Controllers\ReportProductController::class, 'sellerReport'])->name('reports.seller');
        Route::get('/inventory', [App\Http\Controllers\ReportProductController::class, 'inventoryReport'])->name('reports.inventory');

        Route::post('/excel/export', [App\Http\Controllers\ReportProductController::class, 'exportExcel'])->name('reports.export.excel');
        Route::post('/order/excel/export', [App\Http\Controllers\ReportProductController::class, 'OrderExportExcel'])->name('orderreports.export.excel');
        Route::post('/seller/excel/export', [App\Http\Controllers\ReportProductController::class, 'sellerExportExcel'])->name('sellerreports.export.excel');
        Route::post('/inventory/excel/export', [App\Http\Controllers\ReportProductController::class, 'inventoryExportExcel'])->name('inventoryreports.export.excel');

        Route::get('/admin-reports', [App\Http\Controllers\ReportProductController::class, 'adminDashboardReports'])->name('reports.dashboard');

    });

    Route::prefix('payment')->group(function () {
        Route::get('/', [App\Http\Controllers\PaymentController::class, 'index'])->name('payment');
        Route::post('/view', [App\Http\Controllers\PaymentController::class, 'paymentView'])->name('payment.view');
        Route::post('/export/excel', [App\Http\Controllers\PaymentController::class, 'export'])->name('payment.export.excel')->middleware(['checkAccess:export']);
    });

    Route::prefix('request-details')->group(function () {
        Route::get('/{type}', [App\Http\Controllers\CustomerRequestController::class, 'index'])->name('request-details');
    });
    Route::prefix('merchants')->middleware(['checkAccess:visible'])->group(function () {
        Route::get('/', [App\Http\Controllers\MerchantController::class, 'index'])->name('merchants');
        // Route::get('/merchants-list', [App\Http\Controllers\MerchantController::class, 'index'])->name('merchantsList');
        // Route::get('/merchants-list', [App\Http\Controllers\MerchantController::class, 'index'])->name('merchantsList');
        Route::get('/view/products/{id?}', [App\Http\Controllers\MerchantController::class, 'viewMerchantProducts'])->name('merchant.products.view');
        Route::get('/view/orders/{id?}', [App\Http\Controllers\MerchantController::class, 'viewMerchantOrders'])->name('merchant.orders.view');
        Route::get('/merchant/orders', [App\Http\Controllers\MerchantController::class, 'merchantOrders'])->name('merchant-orders');
        // Route::post('/quantityChange',[App\Http\Controllers\MerchantController::class, 'quantityChange'])->name('quantityChange');
        Route::post('/lowStockChange', [App\Http\Controllers\MerchantController::class, 'lowStockChange'])->name('lowStockChange');
        Route::post('/product/status', [App\Http\Controllers\MerchantController::class, 'changeStatus'])->name('merchant.products.status');
        Route::post('/product/delete', [App\Http\Controllers\MerchantController::class, 'deleteMerchantProduct'])->name('merchant.products.delete');
        Route::post('/view', [App\Http\Controllers\MerchantController::class, 'orderView'])->name('merchant_order.view');
        Route::post('/open/orderStatus/modal', [App\Http\Controllers\MerchantController::class, 'openOrderStatusModal'])->name('merchant-order.status.modal');
        Route::post('/export/excel', [App\Http\Controllers\MerchantController::class, 'export'])->name('merchants.export.excel')->middleware(['checkAccess:export']);
        Route::post('/export', [App\Http\Controllers\MerchantController::class, 'orderexport'])->name('merchant-orders.export.excel')->middleware(['checkAccess:orderexport']);
    });
    Route::get('product-requests', [App\Http\Controllers\ProductRequestController::class, 'index'])->name('product-requests');

    Route::get('/zone', [App\Http\Controllers\ZoneController::class, 'index'])->name('zone')->middleware(['checkAccess:visible']);
    Route::post('/zone/addOrEdit', [App\Http\Controllers\ZoneController::class, 'modalAddEdit'])->name('zone.add.edit')->middleware(['checkAccess:editable']);
    Route::post('/zone/status', [App\Http\Controllers\ZoneController::class, 'changeStatus'])->name('zone.status')->middleware(['checkAccess:status']);
    Route::post('/zone/delete', [App\Http\Controllers\ZoneController::class, 'delete'])->name('zone.delete')->middleware(['checkAccess:delete']);
    Route::post('/zone/save', [App\Http\Controllers\ZoneController::class, 'saveForm'])->name('zone.save')->middleware(['checkAccess:editable']);
    Route::post('/zone/export/excel', [App\Http\Controllers\ZoneController::class, 'export'])->name('zone.export.excel')->middleware(['checkAccess:export']);
    Route::get('/zone/export/pdf', [App\Http\Controllers\ZoneController::class, 'exportPdf'])->name('zone.export.pdf')->middleware(['checkAccess:export']);
});



Route::get('razorpay-payment', [RazorpayPaymentController::class, 'index']);
Route::post('razorpay/process', [RazorpayPaymentController::class, 'razorpay_response'])->name('razorpay.payment.store');
Route::any('/payment/failed', [RazorpayPaymentController::class, 'fail_page'])->name('fail.page');

Route::get('/bulkUpload', [App\Http\Controllers\Master\BulkUploadController::class, 'index'])->name('bulkUpload');
Route::post('/upload/attributes/state', [App\Http\Controllers\Master\BulkUploadController::class, 'doAttributesBulkUploadState'])->name('state.bulk.upload');
Route::post('/upload/attributes/city', [App\Http\Controllers\Master\BulkUploadController::class, 'doAttributesBulkUploadCity'])->name('city.bulk.upload');
Route::post('/upload/attributes/pincode', [App\Http\Controllers\Master\BulkUploadController::class, 'doAttributesBulkUploadPincode'])->name('pincode.bulk.upload');

Route::get('/getAreas/{id}', [ZoneController::class, 'getAreas']);
Route::get('/getPincodes/{id}', [ZoneController::class, 'getPincodes']);
