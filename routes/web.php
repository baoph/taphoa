<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SanPhamController;
use App\Http\Controllers\DonHangController;
use App\Http\Controllers\BaoCaoController;
use App\Http\Controllers\DonViBanController;
use App\Http\Controllers\SanPhamDonViController;

Route::get('/', function () {
    return redirect()->route('don-hang.index');
});

// Quản lý sản phẩm
Route::resource('san-pham', SanPhamController::class)->parameters([
    'san-pham' => 'sanPham'
]);

// API routes cho sản phẩm
Route::get('api/san-pham/search', [SanPhamController::class, 'search'])->name('san-pham.search');
Route::get('api/san-pham/search-ajax', [SanPhamController::class, 'searchAjax'])->name('san-pham.search.ajax');
Route::get('api/san-pham/{id}/don-vi-options', [SanPhamController::class, 'getDonViOptions'])->name('san-pham.don-vi-options');

// Import Excel
Route::get('san-pham-import', [SanPhamController::class, 'showImportForm'])->name('san-pham.import.form');
Route::post('san-pham-import', [SanPhamController::class, 'import'])->name('san-pham.import');

// Quản lý đơn vị bán
Route::resource('don-vi-ban', DonViBanController::class)->parameters([
    'don-vi-ban' => 'donViBan'
]);

// Quản lý đơn vị sản phẩm
Route::prefix('san-pham-don-vi')->name('san-pham-don-vi.')->group(function () {
    Route::get('{sanPhamId}', [SanPhamDonViController::class, 'index'])->name('index');
    Route::post('/', [SanPhamDonViController::class, 'store'])->name('store');
    Route::put('{sanPhamDonVi}', [SanPhamDonViController::class, 'update'])->name('update');
    Route::delete('{sanPhamDonVi}', [SanPhamDonViController::class, 'destroy'])->name('destroy');
    Route::get('{sanPhamId}/options', [SanPhamDonViController::class, 'getDonViOptions'])->name('options');
});

// Quản lý đơn hàng
Route::get('don-hang', [DonHangController::class, 'index'])->name('don-hang.index');
Route::get('don-hang/list', [DonHangController::class, 'getByDate'])->name('don-hang.list');
Route::post('don-hang', [DonHangController::class, 'store'])->name('don-hang.store');
Route::get('don-hang/{donHang}', [DonHangController::class, 'show'])->name('don-hang.show');
Route::put('don-hang/{donHang}', [DonHangController::class, 'update'])->name('don-hang.update');
Route::delete('don-hang/{donHang}', [DonHangController::class, 'destroy'])->name('don-hang.destroy');

// Báo cáo
Route::get('bao-cao/doanh-thu', [BaoCaoController::class, 'doanhThu'])->name('bao-cao.doanh-thu');
