<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register/customer', 'App\Http\Controllers\Api\CustomerController@registerCustomer');
Route::post('/login', 'App\Http\Controllers\Api\CustomerController@doLogin');

Route::group(['middleware' => 'jwt'], function () {
    Route::post('/get/orders', [App\Http\Controllers\Api\OrderController::class, 'getOrders']);
    Route::post('/get/orderByno', [App\Http\Controllers\Api\OrderController::class, 'getOrderByOrderNo']);
    Route::get('/get/order-item-detail', [App\Http\Controllers\Api\OrderController::class, 'getOrderItemDetail']);
    Route::post('/get/orderData', [App\Http\Controllers\Api\OrderController::class, 'getOrderData']);
    Route::post('/cancel/request/orders', [App\Http\Controllers\Api\OrderController::class, 'requestCancelOrder']);
    Route::post('/cancel/request/all', [App\Http\Controllers\Api\OrderController::class, 'bulkCancelOrder']);
    Route::post('/order/exchange/request', [App\Http\Controllers\Api\OrderController::class, 'requestExchangeOrder']);
    Route::post('/update/profile', [App\Http\Controllers\Api\CustomerController::class, 'updateProfile']);
    Route::post('/update/profile/image', [App\Http\Controllers\Api\CustomerController::class, 'updateProfileImage']);
    Route::post('/change/password', [App\Http\Controllers\Api\CustomerController::class, 'changePassword']);



    Route::post('/add/customer/address', [App\Http\Controllers\Api\CustomerController::class, 'addCustomerAddress']);
    Route::post('/update/customer/address', [App\Http\Controllers\Api\CustomerController::class, 'updateCustomerAddress']);
    Route::post('/get/customer/address', [App\Http\Controllers\Api\CustomerController::class, 'getCustomerAddress']);
    Route::post('/delete/customer/address', [App\Http\Controllers\Api\CustomerController::class, 'deleteCustomerAddress']);
    Route::post('/apply/coupon', [App\Http\Controllers\Api\Couponcontroller::class, 'applyCoupon']);
    Route::post('/set/default/address', [App\Http\Controllers\Api\CustomerController::class, 'setDefaultAddress']);
    Route::get('/customer/me', [App\Http\Controllers\Api\CustomerController::class, 'getCustomerData']);

    Route::post('/get/shipping/rocket/charges', [App\Http\Controllers\Api\CartController::class, 'getShippingRocketCharges']);


    Route::post('/proceed/checkout', [App\Http\Controllers\Api\CheckoutController::class, 'proceedCheckout']);
    Route::post('/verify/payment/signature', [App\Http\Controllers\Api\CheckoutController::class, 'verifySignature']);

    Route::post('/reviews/create', [App\Http\Controllers\Api\ReivewsController::class, 'create']);

});
Route::post('/get/recent/view', [App\Http\Controllers\Api\CollectionController::class, 'getRecentViews']);
Route::post('/set/recent', [App\Http\Controllers\Api\CommonController::class, 'setRecentView']);
Route::post('/track/order', [App\Http\Controllers\Api\OrderController::class, 'trackShipment']);
Route::get('/get/cancel/reason', [App\Http\Controllers\Api\OrderController::class, 'getCancelReason']);

/**

 * ccave routes

 */
Route::post('/assign/order', [App\Http\Controllers\Api\CheckoutController::class, 'autoAssignOrder']);

// Route::post('/proceed/ccav/checkout', [App\Http\Controllers\Payment\CCavenueController::class, 'proceedCheckout']);
Route::post('/proceed/ccav/checkout', [App\Http\Controllers\Api\CheckoutController::class, 'proceedCcvCheckout']);
Route::post('/verify/ccav/token', [App\Http\Controllers\Api\CheckoutController::class, 'verifyCCavenueTransaction']);
Route::post('/payment/initiate', [App\Http\Controllers\Api\CheckoutController::class, 'startPayment']);
Route::post('/payment/response', [App\Http\Controllers\Api\CheckoutController::class, 'ccavResponseHandler']);
Route::post('/generate-customer-invoice', [App\Http\Controllers\Api\CheckoutController::class, 'generateInvoice']);



Route::post('/generate/otp', [App\Http\Controllers\Api\CustomerController::class, 'generate']);
Route::post('/login/otp', [App\Http\Controllers\Api\CustomerController::class, 'loginWithOtp']);
Route::post('/google/callback', [App\Http\Controllers\Api\CustomerController::class, 'googleCallback']);
Route::get('/google/redirecturl', [App\Http\Controllers\Api\CustomerController::class, 'getGoogleRedirectUrl']);
Route::post('/subscribe/newsletter', [App\Http\Controllers\Api\CustomerController::class, 'subscribeNewsleter']);




Route::post('/add/cart', [App\Http\Controllers\Api\CartController::class, 'addToCart']);
Route::post('/bulk-add-cart', [App\Http\Controllers\Api\CartController::class, 'bulkAddToCart']);
Route::post('/update/cart', [App\Http\Controllers\Api\CartController::class, 'updateCart']);
Route::post('/delete/cart', [App\Http\Controllers\Api\CartController::class, 'deleteCart']);
Route::post('/get/cart', [App\Http\Controllers\Api\CartController::class, 'getCarts']);
Route::post('/clear/cart', [App\Http\Controllers\Api\CartController::class, 'clearCart']);
Route::post('/update/cartAmount', [App\Http\Controllers\Api\CartController::class, 'updateCartAmount']);

Route::get('/get/shipping/type', [App\Http\Controllers\Api\CheckoutController::class, 'getShippingType']);
Route::get('/get/site/info', [App\Http\Controllers\Api\SiteController::class, 'siteInfo']);
Route::get('/get/home/details', [App\Http\Controllers\Api\CommonController::class, 'getAllHomeDetails']);
Route::get('/get/search/data', [App\Http\Controllers\Api\CommonController::class, 'getSearchData']);


Route::get('/get/topMenu/{slug?}', [App\Http\Controllers\Api\MenuController::class, 'getTopMenu']);
Route::get('/get/allMenu', [App\Http\Controllers\Api\MenuController::class, 'getAllMenu']);

Route::get('/get/testimonials', [App\Http\Controllers\Api\CommonController::class, 'getAllTestimonials']);
Route::get('/get/history', [App\Http\Controllers\Api\CommonController::class, 'getAllHistoryVideo']);
Route::get('/get/banners', [App\Http\Controllers\Api\CommonController::class, 'getAllBanners']);

Route::get('/get/home/brands', [App\Http\Controllers\Api\CommonController::class, 'getHomePageBrands']);
Route::get('/get/brands', [App\Http\Controllers\Api\CommonController::class, 'getAllBrands']);
Route::get('/get/brands/all/{slug}', [App\Http\Controllers\Api\CommonController::class, 'getBrandInfo']);
Route::get('/get/brands/alphabets', [App\Http\Controllers\Api\CommonController::class, 'getBrandByAlphabets']);
Route::post('/dynamic-brand-data', [App\Http\Controllers\Api\CommonController::class, 'getBrandPageDynamicData']);

Route::get('/get/discount/collections', [App\Http\Controllers\Api\CommonController::class, 'getDiscountCollections']);

Route::get('/get/product/collections/{order_by?}', [App\Http\Controllers\Api\CollectionController::class, 'getProductCollections']);
Route::get('/get/product/collections/byorder/{order_by}', [App\Http\Controllers\Api\CollectionController::class, 'getProductCollectionByOrder']);

Route::get('/get/filter/static/sidemenus', [App\Http\Controllers\Api\FilterController::class, 'getFilterStaticSideMenu']);
Route::get('/get/products', [App\Http\Controllers\Api\FilterController::class, 'getProducts']);
Route::get('/get/products/by/slug/{product_url}', [App\Http\Controllers\Api\FilterController::class, 'getProductBySlug']);

Route::get('/get/states', [App\Http\Controllers\Api\CommonController::class, 'getSates']);
Route::get('/get/cities', [App\Http\Controllers\Api\CommonController::class, 'getCities']);
Route::get('/get/pincode', [App\Http\Controllers\Api\CommonController::class, 'getPincode']);
Route::post('/get/meta', [App\Http\Controllers\Api\CommonController::class, 'getMetaInfo']);
Route::get('/get/topbar', [App\Http\Controllers\Api\CommonController::class, 'getTopBar']);

Route::post('/get/global/search', [App\Http\Controllers\Api\FilterController::class, 'globalSearch']);
Route::post('/get/other/category', [App\Http\Controllers\Api\FilterController::class, 'getOtherCategories']);
Route::post('/get/dynamic/filter/category', [App\Http\Controllers\Api\FilterController::class, 'getDynamicFilterCategory']);


Route::post('/send/password/link', [App\Http\Controllers\Api\CustomerController::class, 'sendPasswordLink']);
Route::post('/reset/password', [App\Http\Controllers\Api\CustomerController::class, 'resetPasswordLink']);
Route::post('/check/tokenValid', [App\Http\Controllers\Api\CustomerController::class, 'checkValidToken']);
Route::get('/get/quickLink', [App\Http\Controllers\Api\QuickLinkController::class, 'index']);
Route::get('/get/orderCancelReason', [App\Http\Controllers\Api\OrderCancelController::class, 'index']);
Route::get('/exchange-reasons', [App\Http\Controllers\Api\ExchangeStatusController::class, 'index']);

Route::post('/verify/account', [App\Http\Controllers\Api\CustomerController::class, 'verifyAccount']);

Route::post('/get/shipping/charges', [App\Http\Controllers\Api\CartController::class, 'getShippingCharges']);
Route::post('/get/delivery', [App\Http\Controllers\Api\CartController::class, 'getDeliveryCharges']);
Route::post('/get/collection/colorcode', [App\Http\Controllers\Api\CollectionController::class, 'productCollectionByColorCode']);
Route::post('/get/accessories/products', [App\Http\Controllers\Api\FilterController::class, 'getAccessoriesProductsByCategory']);

Route::get('/home-banners', [App\Http\Controllers\Api\CommonController::class, 'getHomeBanners']);
Route::get('/ecom-home-details', [App\Http\Controllers\Api\CommonController::class, 'getEcomHomeDetails']);
Route::get('/login-banners', [App\Http\Controllers\Api\CommonController::class, 'getLoginBanners']);

Route::get('/home-categories', [App\Http\Controllers\Api\FilterController::class, 'getCategoriesSectionHome']);

Route::get('/top-selling-products', [App\Http\Controllers\Api\CollectionController::class, 'getTopSellingProducts']);

Route::get('/discount-products', [App\Http\Controllers\Api\CommonController::class, 'getDiscountedProducts']);



Route::middleware(['client'])->group(function () {
});

Route::post('/customer/request', [App\Http\Controllers\CustomerRequestController::class, 'createForm']);
