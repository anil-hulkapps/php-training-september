<?php

use App\Http\Controllers\AvalaraController;
use Illuminate\Support\Facades\Route;

Route::post('/create', [AvalaraController::class, 'create']);
Route::post('/create-test', [AvalaraController::class, 'createTest']);
Route::post('/failover-metafield-create', [AvalaraController::class, 'failoverMetafieldCreate']);
