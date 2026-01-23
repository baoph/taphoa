<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SanPhamController;
use App\Http\Controllers\DonHangController;
use App\Http\Controllers\BaoCaoController;

Route::get('/', function () {
    return redirect()->route('don-hang.index');
});

// Quản lý sản phẩm
Route::resource('san-pham', SanPhamController::class)->parameters([
    'san-pham' => 'sanPham'
]);
Route::get('api/san-pham/search', [SanPhamController::class, 'search'])->name('san-pham.search');

// Quản lý đơn hàng
Route::get('don-hang', [DonHangController::class, 'index'])->name('don-hang.index');
Route::get('don-hang/list', [DonHangController::class, 'getByDate'])->name('don-hang.list');
Route::post('don-hang', [DonHangController::class, 'store'])->name('don-hang.store');
Route::get('don-hang/{donHang}', [DonHangController::class, 'show'])->name('don-hang.show');
Route::put('don-hang/{donHang}', [DonHangController::class, 'update'])->name('don-hang.update');
Route::delete('don-hang/{donHang}', [DonHangController::class, 'destroy'])->name('don-hang.destroy');

// Báo cáo
Route::get('bao-cao/doanh-thu', [BaoCaoController::class, 'doanhThu'])->name('bao-cao.doanh-thu');
