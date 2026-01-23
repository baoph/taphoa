<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SanPhamController;
use App\Http\Controllers\DonHangController;
use App\Http\Controllers\BaoCaoController;

Route::get('/', function () {
    return redirect()->route('don-hang.index');
});

// Quản lý sản phẩm
Route::resource('san-pham', SanPhamController::class)->except(['show']);

// API lấy danh sách sản phẩm cho Select2
Route::get('/api/san-pham', [SanPhamController::class, 'getSanPham'])->name('api.san-pham');

// Quản lý đơn hàng
Route::get('/don-hang', [DonHangController::class, 'index'])->name('don-hang.index');
Route::post('/don-hang', [DonHangController::class, 'store'])->name('don-hang.store');
Route::put('/don-hang/{donHang}', [DonHangController::class, 'update'])->name('don-hang.update');
Route::delete('/don-hang/{donHang}', [DonHangController::class, 'destroy'])->name('don-hang.destroy');

// Báo cáo
Route::get('/bao-cao', [BaoCaoController::class, 'index'])->name('bao-cao.index');
