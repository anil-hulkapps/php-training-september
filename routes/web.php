<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\SettingController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::match(
    ['get', 'post'], '/authenticate', [ AuthController::class, 'authenticate']
)->name('authenticate');

Route::post('/webhook/{type}', [\Osiset\ShopifyApp\Http\Controllers\WebhookController::class, 'handle'])->middleware(\Illuminate\Routing\Middleware\SubstituteBindings::class)->name('webhook');

Route::get('flush', function() {
    request()->session()->flush();
});

//Route::get('test-order-cancel', [\App\Http\Controllers\DashboardController::class, 'testOrderCancelled']);
//Route::get('test-fulfill-create', [\App\Http\Controllers\DashboardController::class, 'testFulfillmentCreate']);
//Route::get('test-refund-create', [\App\Http\Controllers\DashboardController::class, 'testRefundCreate']);

Route::group(['middleware' => 'auth.shopify.custom'], function () {

    //Custom Routes
    Route::get('/', function () {
        return view('main');
    })->name('home');

    Route::get('{url?}', function () {
        return view('main');
    })->where('url', '([A-z\d\-\/_.]+)?');

    Route::post('import-product', [ProductController::class, 'importProducts']);

    //Route::match(['GET', 'POST'], 'step-1', 'SettingController@Steps');
    //Route::get('/step-1', [SettingController::class, 'Steps']);
});
