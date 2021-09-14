<?php

use App\Http\Controllers\AvalaraController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TransactionController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth.shopify.custom']], function () {
    Route::group(['prefix' => 'settings'], function () {
        Route::post('test-connection', [SettingController::class, 'testConnection']);
        Route::match(['GET', 'POST'],'step', [SettingController::class, 'Steps']);
        Route::match(['GET'],'store-detail', [SettingController::class, 'ShopDetail']);
        Route::match(['GET'],'check-connection', [SettingController::class, 'CheckConnection']);
    });
    Route::group(['prefix' => 'products'], function () {
       Route::get('/list', [ProductController::class, 'listProduct']);
    });
    Route::group(['prefix' => 'transactions'], function () {
        Route::get('/orders', [TransactionController::class, 'orders']);
        Route::get('/excise-errors', [TransactionController::class, 'exciseErrors']);
        Route::get('/ignored-orders', [TransactionController::class, 'ignoredOrders']);
        Route::post('/ignore-excise', [TransactionController::class, 'ignoreExcise']);
        Route::post('/reattempt-excise', [TransactionController::class, 'reattemptExcise']);
    });
    //For creating new transactions on Avalara from the checkout page
    Route::post('process-transaction', [AvalaraController::class, 'processTransaction']);
});

Route::post('/create', [AvalaraController::class, 'create']);
Route::post('/create-test', [AvalaraController::class, 'createTest']);
