<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DonViBanController;
use App\Http\Controllers\SanPhamDonViController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// API Đơn vị bán - Lấy danh sách đơn vị bán (cho dropdown)
Route::get('/don-vi-ban', [DonViBanController::class, 'apiIndex']);

// API Sản phẩm - Đơn vị
Route::post('/san-pham-don-vi', [SanPhamDonViController::class, 'store']);
Route::get('/san-pham-don-vi/{sanPhamDonVi}', [SanPhamDonViController::class, 'show']);
Route::put('/san-pham-don-vi/{sanPhamDonVi}', [SanPhamDonViController::class, 'update']);
Route::delete('/san-pham-don-vi/{sanPhamDonVi}', [SanPhamDonViController::class, 'destroy']);

// API Sản phẩm - Danh sách đơn vị của sản phẩm
Route::get('/san-pham/{sanPhamId}/don-vi-list', [SanPhamDonViController::class, 'index']);
Route::get('/san-pham/{sanPhamId}/don-vi-options', [SanPhamDonViController::class, 'getDonViOptions']);
