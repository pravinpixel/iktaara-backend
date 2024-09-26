<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;


// Route::get('/',function(){
//     return view("seller_auth.login");
// });
Route::get('/', function(){
    return redirect('seller/login');
});

/*
Route::middleware(['guest.seller:merchant'])->group(function() {
    Route::get('/login', [App\Http\Controllers\Sellers\Auth\LoginController::class, 'sellerLoginForm'])->name('seller.login');
    Route::post('/seller_login', [App\Http\Controllers\Sellers\Auth\LoginController::class, 'sellerLogin'])->name('sellerLogin');
});

Route::middleware(['auth.seller:merchant'])->group(function(){

    Route::get('/logout',[App\Http\Controllers\Sellers\SellerController::class, 'sellerLogout'])->name('sellerLogout');
    Route::get('/dashboard', [App\Http\Controllers\Sellers\Auth\LoginController::class, 'sellerDashboard']);
});
Route::get('/register', [App\Http\Controllers\Sellers\SellerController::class, 'register'])->name('register');


Route::post('/postRegister', [App\Http\Controllers\Sellers\SellerController::class, 'postRegister'])->name('sellerRegister');


// Route::middleware(['guest:merchant'])->group(function () {
//     Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'sellerLoginForm'])->name('seller.login');

//     // Other merchant authentication routes...
// });

*/