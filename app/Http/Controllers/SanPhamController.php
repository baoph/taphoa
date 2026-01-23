<?php

namespace App\Http\Controllers;

use App\Models\SanPham;
use Illuminate\Http\Request;

class SanPhamController extends Controller
{
    public function index()
    {
        $sanPhams = SanPham::orderBy('ten_san_pham')->paginate(15);
        return view('san-pham.index', compact('sanPhams'));
    }

    public function create()
    {
        return view('san-pham.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'ten_san_pham' => 'required|string|max:255',
            'dvt' => 'required|string|max:50',
            'gia_nhap' => 'required|numeric|min:0',
            'gia_ban' => 'required|numeric|min:0',
        ]);

        SanPham::create($request->all());

        return redirect()->route('san-pham.index')
            ->with('success', 'Thêm sản phẩm thành công!');
    }

    public function edit(SanPham $sanPham)
    {
        return view('san-pham.edit', compact('sanPham'));
    }

    public function update(Request $request, SanPham $sanPham)
    {
        $request->validate([
            'ten_san_pham' => 'required|string|max:255',
            'dvt' => 'required|string|max:50',
            'gia_nhap' => 'required|numeric|min:0',
            'gia_ban' => 'required|numeric|min:0',
        ]);

        $sanPham->update($request->all());

        return redirect()->route('san-pham.index')
            ->with('success', 'Cập nhật sản phẩm thành công!');
    }

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
}
