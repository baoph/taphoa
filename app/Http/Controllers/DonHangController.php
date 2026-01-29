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

        // ========== THÊM MỚI: Tính tổng giá vốn và lợi nhuận ==========
        $tongGiaVon = $donHangs->sum(function ($item) {
            return $item->so_luong * $item->gia_nhap;
        });

        $tongLoiNhuan = $tongTien - $tongGiaVon;
        $tyLeLoiNhuan = $tongTien > 0 ? ($tongLoiNhuan / $tongTien) * 100 : 0;
        // ==============================================================

        // Lấy danh sách sản phẩm cho filter dropdown
        $sanPhams = SanPham::orderBy('ten_san_pham')->get();

        if ($request->ajax()) {
            return response()->json([
                'donHangs' => $donHangs,
                'tongTien' => $tongTien,
                'tongGiaVon' => $tongGiaVon,        // ← THÊM MỚI
                'tongLoiNhuan' => $tongLoiNhuan,    // ← THÊM MỚI
                'tyLeLoiNhuan' => $tyLeLoiNhuan,    // ← THÊM MỚI
            ]);
        }

        return view('don-hang.index', compact(
            'donHangs',
            'ngay',
            'tongTien',
            'tongGiaVon',      // ← THÊM MỚI
            'tongLoiNhuan',    // ← THÊM MỚI
            'tyLeLoiNhuan',    // ← THÊM MỚI
            'sanPhams',
            'tuNgay',
            'denNgay',
            'sanPhamId',
            'isFiltering'
        ));
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

        // ========== THÊM MỚI: Tính giá vốn theo đơn vị bán ==========
        // Công thức: gia_von_don_vi_ban = gia_nhap_co_ban / ti_le_quy_doi
        // Ví dụ: 1 thùng bia giá nhập 240,000đ, 1 thùng = 24 lon
        //        → Giá vốn khi bán theo lon = 240,000 / 24 = 10,000đ/lon
        $giaNhap = 0;
        if ($request->san_pham_id) {
            $sanPham = SanPham::find($request->san_pham_id);
            if ($sanPham) {
                // Lấy giá nhập đơn vị cơ bản (ví dụ: giá nhập 1 thùng)
                $giaNhapCoBan = $sanPham->gia_nhap ?? 0;

                // Tính giá vốn theo đơn vị bán
                if ($request->don_vi_ban_id) {
                    $sanPhamDonViNhap = \App\Models\SanPhamDonVi::where('san_pham_id', $request->san_pham_id)->orderBy('ti_le_quy_doi', 'desc')->first();
                    $giaNhapCoBan = $giaNhapCoBan / $sanPhamDonViNhap->ti_le_quy_doi;

                    $sanPhamDonVi = \App\Models\SanPhamDonVi::where('san_pham_id', $request->san_pham_id)
                        ->where('id', $request->don_vi_ban_id)
                        ->first();
                    if ($sanPhamDonVi->id == $sanPhamDonViNhap->id) {
                        $giaNhap = $sanPham->gia_nhap;
                    } elseif ($sanPhamDonVi != $sanPhamDonViNhap) {
                        $giaNhap = $giaNhapCoBan * $sanPhamDonVi->ti_le_quy_doi;
                    }
                } else {
                    // Không có đơn vị bán, dùng giá nhập cơ bản
                    $giaNhap = $giaNhapCoBan;
                }

                // Trừ tồn kho
                if (!$sanPham->truTonKho($soLuongQuyDoi)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không đủ hàng trong kho!',
                    ], 422);
                }
            }
        }
        // ============================================================

        $donHang = DonHang::create([
            'san_pham_id' => $request->san_pham_id,
            'ten_san_pham' => $request->ten_san_pham,
            'so_luong' => $request->so_luong,
            'gia' => $request->gia,
            'gia_nhap' => $giaNhap,  // ← THÊM MỚI
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
                'gia_nhap' => $donHang->gia_nhap,  // ← THÊM MỚI
                'thanh_tien' => $donHang->thanh_tien,
                'loi_nhuan' => $donHang->loi_nhuan,  // ← THÊM MỚI
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

        // ========== TÍNH GIÁ VỐN THEO ĐƠN VỊ BÁN (GIỐNG STORE) ==========
        // Công thức: 
        // 1. Tìm đơn vị lớn nhất (ti_le_quy_doi cao nhất) - đây là đơn vị nhập hàng
        // 2. Tính giá vốn đơn vị nhỏ nhất = giá nhập / ti_le_quy_doi lớn nhất
        // 3. Nếu bán đơn vị lớn nhất → giá vốn = giá nhập gốc
        //    Ngược lại → giá vốn = giá vốn đơn vị nhỏ nhất × ti_le_quy_doi đơn vị bán
        // Ví dụ: 1 thùng bia giá nhập 240,000đ, 1 thùng = 24 lon
        //        → Giá vốn khi bán theo lon = 240,000 / 24 = 10,000đ/lon
        //        → Giá vốn khi bán theo lốc (6 lon) = 10,000 × 6 = 60,000đ/lốc
        $giaNhap = $donHang->gia_nhap; // Giữ nguyên giá cũ nếu không có sản phẩm
        if ($request->san_pham_id) {
            $sanPham = SanPham::find($request->san_pham_id);
            if ($sanPham) {
                // Lấy giá nhập đơn vị cơ bản (ví dụ: giá nhập 1 thùng)
                $giaNhapCoBan = $sanPham->gia_nhap ?? 0;

                // Tính giá vốn theo đơn vị bán
                if ($request->don_vi_ban_id) {
                    // Tìm đơn vị nhập hàng (đơn vị có ti_le_quy_doi lớn nhất)
                    $sanPhamDonViNhap = \App\Models\SanPhamDonVi::where('san_pham_id', $request->san_pham_id)
                        ->orderBy('ti_le_quy_doi', 'desc')
                        ->first();
                    
                    if ($sanPhamDonViNhap && $sanPhamDonViNhap->ti_le_quy_doi > 0) {
                        // Tính giá vốn đơn vị nhỏ nhất
                        $giaVonDonViNhoNhat = $giaNhapCoBan / $sanPhamDonViNhap->ti_le_quy_doi;
                        
                        // Tìm đơn vị bán hiện tại
                        $sanPhamDonVi = \App\Models\SanPhamDonVi::where('san_pham_id', $request->san_pham_id)
                            ->where('id', $request->don_vi_ban_id)
                            ->first();
                        
                        if ($sanPhamDonVi) {
                            if ($sanPhamDonVi->id == $sanPhamDonViNhap->id) {
                                // Nếu bán theo đơn vị lớn nhất (đơn vị nhập), dùng giá nhập gốc
                                $giaNhap = $giaNhapCoBan;
                            } else {
                                // Ngược lại, tính giá vốn = giá vốn đơn vị nhỏ nhất × ti_le_quy_doi
                                $giaNhap = $giaVonDonViNhoNhat * $sanPhamDonVi->ti_le_quy_doi;
                            }
                        } else {
                            $giaNhap = $giaNhapCoBan;
                        }
                    } else {
                        $giaNhap = $giaNhapCoBan;
                    }
                } else {
                    // Không có đơn vị bán, dùng giá nhập cơ bản
                    $giaNhap = $giaNhapCoBan;
                }

                // Trừ tồn kho mới
                if (!$sanPham->truTonKho($soLuongQuyDoi)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không đủ hàng trong kho!',
                    ], 422);
                }
            }
        }
        // ============================================================

        $donHang->update([
            'san_pham_id' => $request->san_pham_id,
            'ten_san_pham' => $request->ten_san_pham,
            'so_luong' => $request->so_luong,
            'gia' => $request->gia,
            'gia_nhap' => $giaNhap,  // ← THÊM MỚI
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
                'gia_nhap' => $donHang->gia_nhap,  // ← THÊM MỚI
                'thanh_tien' => $donHang->thanh_tien,
                'loi_nhuan' => $donHang->loi_nhuan,  // ← THÊM MỚI
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
