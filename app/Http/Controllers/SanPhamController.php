<?php

namespace App\Http\Controllers;

use App\Models\SanPham;
use App\Models\DonViTinh;
use Illuminate\Http\Request;

class SanPhamController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm
     */
    public function index()
    {
        $sanPhams = SanPham::orderBy('ten_san_pham')->paginate(15);
        return view('san-pham.index', compact('sanPhams'));
    }

    /**
     * Form thêm sản phẩm mới
     */
    public function create()
    {
        $donViTinhs = DonViTinh::orderBy('ten_don_vi')->get();
        return view('san-pham.create', compact('donViTinhs'));
    }

    /**
     * Lưu sản phẩm mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'ten_san_pham' => 'required|string|max:255',
            'dvt' => 'nullable|string|max:50',
            'gia_nhap' => 'nullable|numeric|min:0',
            'gia_ban' => 'nullable|numeric|min:0',
            'gia_ban_le' => 'nullable|numeric|min:0',
            'so_luong' => 'nullable|integer|min:0',
            'ti_so_chuyen_doi' => 'nullable|integer|min:1',
            'ghi_chu' => 'nullable|string',
        ]);

        $data = $request->all();
        
        // Tính toán số lượng đơn vị tự động
        $soLuong = $request->input('so_luong', 0);
        $tiSoChuyenDoi = $request->input('ti_so_chuyen_doi', 1);
        $data['so_luong_don_vi'] = $soLuong * $tiSoChuyenDoi;

        SanPham::create($data);

        return redirect()->route('san-pham.index')
            ->with('success', 'Thêm sản phẩm thành công!');
    }

    /**
     * Form sửa sản phẩm
     */
    public function edit(SanPham $sanPham)
    {
        $donViTinhs = DonViTinh::orderBy('ten_don_vi')->get();
        return view('san-pham.edit', compact('sanPham', 'donViTinhs'));
    }

    /**
     * Cập nhật sản phẩm
     */
    public function update(Request $request, SanPham $sanPham)
    {
        $request->validate([
            'ten_san_pham' => 'required|string|max:255',
            'dvt' => 'nullable|string|max:50',
            'gia_nhap' => 'nullable|numeric|min:0',
            'gia_ban' => 'nullable|numeric|min:0',
            'gia_ban_le' => 'nullable|numeric|min:0',
            'so_luong' => 'nullable|integer|min:0',
            'ti_so_chuyen_doi' => 'nullable|integer|min:1',
            'ghi_chu' => 'nullable|string',
        ]);

        $data = $request->all();
        
        // Tính toán số lượng đơn vị tự động
        $soLuong = $request->input('so_luong', 0);
        $tiSoChuyenDoi = $request->input('ti_so_chuyen_doi', 1);
        $data['so_luong_don_vi'] = $soLuong * $tiSoChuyenDoi;

        $sanPham->update($data);

        return redirect()->route('san-pham.index')
            ->with('success', 'Cập nhật sản phẩm thành công!');
    }

    /**
     * Xóa sản phẩm
     */
    public function destroy(SanPham $sanPham)
    {
        $sanPham->delete();

        return redirect()->route('san-pham.index')
            ->with('success', 'Xóa sản phẩm thành công!');
    }

    public function getSanPham(Request $request)
    {
        $search = $request->get('q', '');
        $sanPhams = SanPham::where('ten_san_pham', 'like', "%{$search}%")
            ->select('id', 'ten_san_pham as text', 'gia_ban')
            ->limit(20)
            ->get();

        return response()->json(['results' => $sanPhams]);
      }
    /**
     * API: Tìm kiếm sản phẩm cho Select2
     */
    public function search(Request $request)
    {
        $search = $request->get('q', '');
        
        $sanPhams = SanPham::where('ten_san_pham', 'like', '%' . $search . '%')
            ->orderBy('ten_san_pham')
            ->limit(20)
            ->get(['id', 'ten_san_pham', 'gia_ban']);

        return response()->json([
            'results' => $sanPhams->map(function ($sp) {
                return [
                    'id' => $sp->ten_san_pham,
                    'text' => $sp->ten_san_pham,
                    'gia_ban' => $sp->gia_ban,
                ];
            })
        ]);
    }
}
