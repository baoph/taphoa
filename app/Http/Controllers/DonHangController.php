<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DonHangController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng trong ngày
     */
    public function index(Request $request)
    {
        $ngay = $request->get('ngay', Carbon::today()->format('Y-m-d'));

        $donHangs = DonHang::whereDate('ngay_ban', $ngay)
            ->orderBy('created_at', 'desc')
            ->get();

        $tongTien = $donHangs->sum(function ($item) {
            return $item->so_luong * $item->gia;
        });

        if ($request->ajax()) {
            return response()->json([
                'donHangs' => $donHangs,
                'tongTien' => $tongTien
            ]);
        }

        return view('don-hang.index', compact('donHangs', 'ngay', 'tongTien'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'san_pham_id' => 'nullable|exists:san_pham,id',
            'ten_san_pham' => 'required|string|max:255',
            'so_luong' => 'required|integer|min:1',
            'gia' => 'required|numeric|min:0',
            'ngay_ban' => 'required|date',
        ]);

        $donHang = DonHang::create([
            'san_pham_id' => $request->san_pham_id,
            'ten_san_pham' => $request->ten_san_pham,
            'so_luong' => $request->so_luong,
            'gia' => $request->gia,
            'ngay_ban' => $request->ngay_ban,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm đơn hàng thành công!',
            'donHang' => $donHang,
            'data' => [
                'id' => $donHang->id,
                'ten_san_pham' => $donHang->ten_san_pham,
                'so_luong' => $donHang->so_luong,
                'gia' => $donHang->gia,
                'thanh_tien' => $donHang->so_luong * $donHang->gia,
                'ngay_ban' => $donHang->ngay_ban->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * API: Lấy thông tin đơn hàng
     */
    public function show(DonHang $donHang)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $donHang->id,
                'san_pham_id' => $donHang->san_pham_id,
                'ten_san_pham' => $donHang->ten_san_pham,
                'so_luong' => $donHang->so_luong,
                'gia' => $donHang->gia,
                'ngay_ban' => $donHang->ngay_ban->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * API: Cập nhật đơn hàng
     */
    public function update(Request $request, DonHang $donHang)
    {
        $request->validate([
            'san_pham_id' => 'nullable|exists:san_pham,id',
            'ten_san_pham' => 'required|string|max:255',
            'so_luong' => 'required|integer|min:1',
            'gia' => 'required|numeric|min:0',
            'ngay_ban' => 'required|date',
        ]);

        $donHang->update([
            'san_pham_id' => $request->san_pham_id,
            'ten_san_pham' => $request->ten_san_pham,
            'so_luong' => $request->so_luong,
            'gia' => $request->gia,
            'ngay_ban' => $request->ngay_ban,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật đơn hàng thành công!',
            'data' => [
                'id' => $donHang->id,
                'ten_san_pham' => $donHang->ten_san_pham,
                'so_luong' => $donHang->so_luong,
                'gia' => $donHang->gia,
                'thanh_tien' => $donHang->so_luong * $donHang->gia,
                'ngay_ban' => $donHang->ngay_ban->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * API: Xóa đơn hàng
     */
    public function destroy(DonHang $donHang)
    {
        $donHang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa đơn hàng thành công!',
        ]);
    }
}
