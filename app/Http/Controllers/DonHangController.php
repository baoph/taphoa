<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DonHangController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng trong ngày hoặc theo filter
     */
    public function index(Request $request)
    {
        $ngay = $request->get('ngay', Carbon::today()->format('Y-m-d'));
        $tuNgay = $request->get('tu_ngay');
        $denNgay = $request->get('den_ngay');
        $sanPhamId = $request->get('san_pham_id');
        $isFiltering = $tuNgay || $denNgay || $sanPhamId;

        // Build query
        $query = DonHang::with('sanPhamDonVi.donViBan');

        if ($isFiltering) {
            // Filter theo khoảng thời gian
            if ($tuNgay) {
                $query->whereDate('ngay_ban', '>=', $tuNgay);
            }
            if ($denNgay) {
                $query->whereDate('ngay_ban', '<=', $denNgay);
            }
            // Filter theo sản phẩm
            if ($sanPhamId) {
                $query->where('san_pham_id', $sanPhamId);
            }
        } else {
            // Hiển thị theo ngày đơn lẻ
            $query->whereDate('ngay_ban', $ngay);
        }

        $donHangs = $query->orderBy('ngay_ban', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $tongTien = $donHangs->sum(function ($item) {
            return $item->so_luong * $item->gia;
        });

        // Lấy danh sách sản phẩm cho filter dropdown
        $sanPhams = SanPham::orderBy('ten_san_pham')->get();

        if ($request->ajax()) {
            return response()->json([
                'donHangs' => $donHangs,
                'tongTien' => $tongTien
            ]);
        }

        return view('don-hang.index', compact('donHangs', 'ngay', 'tongTien', 'sanPhams', 'tuNgay', 'denNgay', 'sanPhamId', 'isFiltering'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'san_pham_id' => 'nullable|exists:san_pham,id',
            'ten_san_pham' => 'required|string|max:255',
            'so_luong' => 'required|numeric|min:0.01',
            'gia' => 'required|numeric|min:0',
            'ngay_ban' => 'required|date',
            'don_vi_ban_id' => 'nullable|exists:don_vi_ban,id',
        ]);

        // Tính số lượng quy đổi về đơn vị cơ bản
        $soLuongQuyDoi = $request->so_luong;
        if ($request->don_vi_ban_id && $request->san_pham_id) {
            $sanPhamDonVi = \App\Models\SanPhamDonVi::where('san_pham_id', $request->san_pham_id)
                ->where('don_vi_ban_id', $request->don_vi_ban_id)
                ->first();

            if ($sanPhamDonVi) {
                $soLuongQuyDoi = $request->so_luong * $sanPhamDonVi->ti_le_quy_doi;
            }
        }

        // Trừ tồn kho
        if ($request->san_pham_id) {
            $sanPham = SanPham::find($request->san_pham_id);
            if ($sanPham) {
                if (!$sanPham->truTonKho($soLuongQuyDoi)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không đủ hàng trong kho!',
                    ], 422);
                }
            }
        }

        $donHang = DonHang::create([
            'san_pham_id' => $request->san_pham_id,
            'ten_san_pham' => $request->ten_san_pham,
            'so_luong' => $request->so_luong,
            'gia' => $request->gia,
            'ngay_ban' => $request->ngay_ban,
            'don_vi_ban_id' => $request->don_vi_ban_id,
            'so_luong_quy_doi' => $soLuongQuyDoi,
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
                'don_vi_ban_id' => $donHang->don_vi_ban_id,
                'so_luong_quy_doi' => $donHang->so_luong_quy_doi,
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
            'so_luong' => 'required|numeric|min:0.01',
            'gia' => 'required|numeric|min:0',
            'ngay_ban' => 'required|date',
            'don_vi_ban_id' => 'nullable|exists:don_vi_ban,id',
        ]);

        // Hoàn lại tồn kho cũ
        if ($donHang->san_pham_id && $donHang->so_luong_quy_doi > 0) {
            $sanPham = SanPham::find($donHang->san_pham_id);
            if ($sanPham) {
                $sanPham->congTonKho($donHang->so_luong_quy_doi);
            }
        }

        // Tính số lượng quy đổi mới
        $soLuongQuyDoi = $request->so_luong;
        if ($request->don_vi_ban_id && $request->san_pham_id) {
            $sanPhamDonVi = \App\Models\SanPhamDonVi::where('san_pham_id', $request->san_pham_id)
                ->where('don_vi_ban_id', $request->don_vi_ban_id)
                ->first();

            if ($sanPhamDonVi) {
                $soLuongQuyDoi = $request->so_luong * $sanPhamDonVi->ti_le_quy_doi;
            }
        }

        // Trừ tồn kho mới
        if ($request->san_pham_id) {
            $sanPham = SanPham::find($request->san_pham_id);
            if ($sanPham) {
                if (!$sanPham->truTonKho($soLuongQuyDoi)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không đủ hàng trong kho!',
                    ], 422);
                }
            }
        }

        $donHang->update([
            'san_pham_id' => $request->san_pham_id,
            'ten_san_pham' => $request->ten_san_pham,
            'so_luong' => $request->so_luong,
            'gia' => $request->gia,
            'ngay_ban' => $request->ngay_ban,
            'don_vi_ban_id' => $request->don_vi_ban_id,
            'so_luong_quy_doi' => $soLuongQuyDoi,
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
        // Hoàn lại tồn kho
        if ($donHang->san_pham_id && $donHang->so_luong_quy_doi > 0) {
            $sanPham = SanPham::find($donHang->san_pham_id);
            if ($sanPham) {
                $sanPham->congTonKho($donHang->so_luong_quy_doi);
            }
        }

        $donHang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa đơn hàng thành công!',
        ]);
    }
}
