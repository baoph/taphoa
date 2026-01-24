<?php

namespace App\Http\Controllers;

use App\Models\DonViBan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DonViBanController extends Controller
{
    /**
     * Hiển thị danh sách đơn vị bán
     */
    public function index(): View
    {
        $donViBans = DonViBan::orderBy('ten_don_vi')->paginate(20);
        return view('don-vi-ban.index', compact('donViBans'));
    }

    /**
     * Hiển thị form tạo mới
     */
    public function create(): View
    {
        return view('don-vi-ban.create');
    }

    /**
     * Lưu đơn vị bán mới
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ten_don_vi' => 'required|string|max:50|unique:don_vi_ban,ten_don_vi',
            'mo_ta' => 'nullable|string',
        ], [
            'ten_don_vi.required' => 'Tên đơn vị không được để trống',
            'ten_don_vi.unique' => 'Đơn vị này đã tồn tại',
        ]);

        DonViBan::create($validated);

        return redirect()->route('don-vi-ban.index')
            ->with('success', 'Thêm đơn vị bán thành công!');
    }

    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit(DonViBan $donViBan): View
    {
        return view('don-vi-ban.edit', compact('donViBan'));
    }

    /**
     * Cập nhật đơn vị bán
     */
    public function update(Request $request, DonViBan $donViBan): RedirectResponse
    {
        $validated = $request->validate([
            'ten_don_vi' => 'required|string|max:50|unique:don_vi_ban,ten_don_vi,' . $donViBan->id,
            'mo_ta' => 'nullable|string',
        ], [
            'ten_don_vi.required' => 'Tên đơn vị không được để trống',
            'ten_don_vi.unique' => 'Đơn vị này đã tồn tại',
        ]);

        $donViBan->update($validated);

        return redirect()->route('don-vi-ban.index')
            ->with('success', 'Cập nhật đơn vị bán thành công!');
    }

    /**
     * Xóa đơn vị bán
     */
    public function destroy(DonViBan $donViBan): RedirectResponse
    {
        try {
            $donViBan->delete();
            return redirect()->route('don-vi-ban.index')
                ->with('success', 'Xóa đơn vị bán thành công!');
        } catch (\Exception $e) {
            return redirect()->route('don-vi-ban.index')
                ->with('error', 'Không thể xóa đơn vị bán này vì đang được sử dụng!');
        }
    }
}
