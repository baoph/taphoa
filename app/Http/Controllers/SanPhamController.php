<?php

namespace App\Http\Controllers;

use App\Models\SanPham;
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
        return view('san-pham.create');
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
        ]);

        SanPham::create($request->all());

        return redirect()->route('san-pham.index')
            ->with('success', 'Thêm sản phẩm thành công!');
    }

    /**
     * Form sửa sản phẩm
     */
    public function edit(SanPham $sanPham)
    {
        return view('san-pham.edit', compact('sanPham'));
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
        ]);

        $sanPham->update($request->all());

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
