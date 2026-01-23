<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DonHangController extends Controller
{
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
            'ten_san_pham' => 'required|string|max:255',
            'so_luong' => 'required|integer|min:1',
            'gia' => 'required|numeric|min:0',
        ]);

        $donHang = DonHang::create([
            'ten_san_pham' => $request->ten_san_pham,
            'so_luong' => $request->so_luong,
            'gia' => $request->gia,
            'ngay_ban' => Carbon::today(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm đơn hàng thành công!',
            'donHang' => $donHang
        ]);
    }

    public function update(Request $request, DonHang $donHang)
    {
        $request->validate([
            'ten_san_pham' => 'required|string|max:255',
            'so_luong' => 'required|integer|min:1',
            'gia' => 'required|numeric|min:0',
        ]);

        $donHang->update($request->only(['ten_san_pham', 'so_luong', 'gia']));

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật đơn hàng thành công!',
            'donHang' => $donHang
        ]);
    }

    public function destroy(DonHang $donHang)
    {
        $donHang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa đơn hàng thành công!'
        ]);
    }
}
