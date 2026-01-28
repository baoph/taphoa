<?php

namespace App\Http\Controllers;

use App\Models\NhapHang;
use App\Models\SanPham;
use App\Models\DonViBan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NhapHangController extends Controller
{
    /**
     * Hiển thị danh sách nhập hàng trong ngày hoặc theo filter
     */
    public function index(Request $request)
    {
        $ngay = $request->get('ngay', Carbon::today()->format('Y-m-d'));
        $tuNgay = $request->get('tu_ngay');
        $denNgay = $request->get('den_ngay');
        $sanPhamId = $request->get('san_pham_id');
        $isFiltering = $tuNgay || $denNgay || $sanPhamId;

        // Build query
        $query = NhapHang::with('donViBan');

        if ($isFiltering) {
            // Filter theo khoảng thời gian
            if ($tuNgay) {
                $query->whereDate('ngay_nhap', '>=', $tuNgay);
            }
            if ($denNgay) {
                $query->whereDate('ngay_nhap', '<=', $denNgay);
            }
            // Filter theo sản phẩm
            if ($sanPhamId) {
                $query->where('san_pham_id', $sanPhamId);
            }
        } else {
            // Hiển thị theo ngày đơn lẻ
            $query->whereDate('ngay_nhap', $ngay);
        }

        $nhapHangs = $query->orderBy('ngay_nhap', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $tongTien = $nhapHangs->sum(function ($item) {
            return $item->so_luong * $item->gia_nhap;
        });

        // Lấy danh sách đơn vị bán cho select
        $donViBans = DonViBan::all();

        // Lấy danh sách sản phẩm cho filter dropdown
        $sanPhams = SanPham::orderBy('ten_san_pham')->get();

        if ($request->ajax()) {
            return response()->json([
                'nhapHangs' => $nhapHangs,
                'tongTien' => $tongTien
            ]);
        }

        return view('nhap-hang.index', compact('nhapHangs', 'ngay', 'tongTien', 'donViBans', 'sanPhams', 'tuNgay', 'denNgay', 'sanPhamId', 'isFiltering'));
    }

    /**
     * API: Lưu nhập hàng mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'san_pham_id' => 'nullable|exists:san_pham,id',
            'ten_san_pham' => 'required|string|max:255',
            'so_luong' => 'required|numeric|min:0.01',
            'gia_nhap' => 'required|numeric|min:0',
            'ngay_nhap' => 'required|date',
            'don_vi_ban_id' => 'nullable|exists:don_vi_ban,id',
            'ghi_chu' => 'nullable|string|max:500',
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

        // Cộng vào tồn kho
        if ($request->san_pham_id) {
            $sanPham = SanPham::find($request->san_pham_id);
            if ($sanPham) {
                $sanPham->congTonKho($soLuongQuyDoi);
            }
        }

        $nhapHang = NhapHang::create([
            'san_pham_id' => $request->san_pham_id,
            'ten_san_pham' => $request->ten_san_pham,
            'so_luong' => $request->so_luong,
            'gia_nhap' => $request->gia_nhap,
            'ngay_nhap' => $request->ngay_nhap,
            'don_vi_ban_id' => $request->don_vi_ban_id,
            'so_luong_quy_doi' => $soLuongQuyDoi,
            'ghi_chu' => $request->ghi_chu,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm nhập hàng thành công!',
            'nhapHang' => $nhapHang,
            'data' => [
                'id' => $nhapHang->id,
                'ten_san_pham' => $nhapHang->ten_san_pham,
                'so_luong' => $nhapHang->so_luong,
                'gia_nhap' => $nhapHang->gia_nhap,
                'thanh_tien' => $nhapHang->so_luong * $nhapHang->gia_nhap,
                'ngay_nhap' => $nhapHang->ngay_nhap->format('Y-m-d'),
                'ghi_chu' => $nhapHang->ghi_chu,
            ],
        ]);
    }

    /**
     * API: Lấy thông tin nhập hàng
     */
    public function show(NhapHang $nhapHang)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $nhapHang->id,
                'san_pham_id' => $nhapHang->san_pham_id,
                'ten_san_pham' => $nhapHang->ten_san_pham,
                'so_luong' => $nhapHang->so_luong,
                'gia_nhap' => $nhapHang->gia_nhap,
                'ngay_nhap' => $nhapHang->ngay_nhap->format('Y-m-d'),
                'don_vi_ban_id' => $nhapHang->don_vi_ban_id,
                'ghi_chu' => $nhapHang->ghi_chu,
            ],
        ]);
    }

    /**
     * API: Cập nhật nhập hàng
     */
    public function update(Request $request, NhapHang $nhapHang)
    {
        $request->validate([
            'san_pham_id' => 'nullable|exists:san_pham,id',
            'ten_san_pham' => 'required|string|max:255',
            'so_luong' => 'required|numeric|min:0.01',
            'gia_nhap' => 'required|numeric|min:0',
            'ngay_nhap' => 'required|date',
            'don_vi_ban_id' => 'nullable|exists:don_vi_ban,id',
            'ghi_chu' => 'nullable|string|max:500',
        ]);

        // Hoàn lại tồn kho cũ (trừ đi số lượng đã nhập)
        if ($nhapHang->san_pham_id && $nhapHang->so_luong_quy_doi > 0) {
            $sanPham = SanPham::find($nhapHang->san_pham_id);
            if ($sanPham) {
                $sanPham->truTonKho($nhapHang->so_luong_quy_doi);
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

        // Cộng tồn kho mới
        if ($request->san_pham_id) {
            $sanPham = SanPham::find($request->san_pham_id);
            if ($sanPham) {
                $sanPham->congTonKho($soLuongQuyDoi);
            }
        }

        $nhapHang->update([
            'san_pham_id' => $request->san_pham_id,
            'ten_san_pham' => $request->ten_san_pham,
            'so_luong' => $request->so_luong,
            'gia_nhap' => $request->gia_nhap,
            'ngay_nhap' => $request->ngay_nhap,
            'don_vi_ban_id' => $request->don_vi_ban_id,
            'so_luong_quy_doi' => $soLuongQuyDoi,
            'ghi_chu' => $request->ghi_chu,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật nhập hàng thành công!',
            'data' => [
                'id' => $nhapHang->id,
                'ten_san_pham' => $nhapHang->ten_san_pham,
                'so_luong' => $nhapHang->so_luong,
                'gia_nhap' => $nhapHang->gia_nhap,
                'thanh_tien' => $nhapHang->so_luong * $nhapHang->gia_nhap,
                'ngay_nhap' => $nhapHang->ngay_nhap->format('Y-m-d'),
                'ghi_chu' => $nhapHang->ghi_chu,
            ],
        ]);
    }

    /**
     * API: Xóa nhập hàng
     */
    public function destroy(NhapHang $nhapHang)
    {
        // Hoàn lại tồn kho (trừ đi số lượng đã nhập)
        if ($nhapHang->san_pham_id && $nhapHang->so_luong_quy_doi > 0) {
            $sanPham = SanPham::find($nhapHang->san_pham_id);
            if ($sanPham) {
                $sanPham->truTonKho($nhapHang->so_luong_quy_doi);
            }
        }

        $nhapHang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa nhập hàng thành công!',
        ]);
    }

    /**
     * API: Lấy tổng tiền nhập hàng theo ngày
     */
    public function getTongTienTheoNgay(Request $request)
    {
        $ngay = $request->get('ngay', Carbon::today()->format('Y-m-d'));

        $nhapHangs = NhapHang::whereDate('ngay_nhap', $ngay)->get();

        $tongTien = $nhapHangs->sum(function ($item) {
            return $item->so_luong * $item->gia_nhap;
        });

        return response()->json([
            'success' => true,
            'ngay' => $ngay,
            'tongTien' => $tongTien,
            'soLuongDon' => $nhapHangs->count(),
        ]);
    }
}
