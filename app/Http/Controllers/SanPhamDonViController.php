<?php

namespace App\Http\Controllers;

use App\Models\SanPham;
use App\Models\SanPhamDonVi;
use App\Models\DonViBan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class SanPhamDonViController extends Controller
{
    /**
     * Lấy danh sách đơn vị của sản phẩm
     */
    public function index(int $sanPhamId): JsonResponse
    {
        $sanPham = SanPham::findOrFail($sanPhamId);
        $donViList = $sanPham->sanPhamDonVi()->with('donViBan')->get();
        return response()->json([
            'success' => true,
            'data' => $donViList,
            'don_vi_co_ban' => $sanPham->don_vi_co_ban,
        ]);
    }

    /**
     * Thêm đơn vị cho sản phẩm
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'san_pham_id' => 'required|exists:san_pham,id',
            'don_vi_ban_id' => 'required|exists:don_vi_ban,id',
            'ti_le_quy_doi' => 'required|numeric|min:0.01',
            'gia_ban' => 'required|numeric|min:0',
        ]);

        // Kiểm tra xem đã tồn tại chưa
        $exists = SanPhamDonVi::where('san_pham_id', $validated['san_pham_id'])
            ->where('don_vi_ban_id', $validated['don_vi_ban_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn vị này đã tồn tại cho sản phẩm!',
            ], 422);
        }

        $sanPhamDonVi = SanPhamDonVi::create($validated);
        $sanPhamDonVi->load('donViBan');

        return response()->json([
            'success' => true,
            'message' => 'Thêm đơn vị thành công!',
            'data' => $sanPhamDonVi,
        ]);
    }

    /**
     * Lấy chi tiết đơn vị sản phẩm
     */
    public function show(SanPhamDonVi $sanPhamDonVi): JsonResponse
    {
        $sanPhamDonVi->load('donViBan');
        return response()->json([
            'success' => true,
            'data' => $sanPhamDonVi,
        ]);
    }

    /**
     * Cập nhật đơn vị sản phẩm
     */
    public function update(Request $request, SanPhamDonVi $sanPhamDonVi): JsonResponse
    {
        $validated = $request->validate([
            'ti_le_quy_doi' => 'required|numeric|min:0.01',
            'gia_ban' => 'required|numeric|min:0',
        ]);

        $sanPhamDonVi->update($validated);
        $sanPhamDonVi->load('donViBan');

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công!',
            'data' => $sanPhamDonVi,
        ]);
    }

    /**
     * Xóa đơn vị sản phẩm
     */
    public function destroy(SanPhamDonVi $sanPhamDonVi): JsonResponse
    {
        try {
            $sanPhamDonVi->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa đơn vị thành công!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa đơn vị này!',
            ], 500);
        }
    }

    /**
     * Lấy danh sách đơn vị bán của sản phẩm (cho dropdown)
     */
    public function getDonViOptions(int $sanPhamId): JsonResponse
    {
        $sanPham = SanPham::findOrFail($sanPhamId);
        $options = $sanPham->getDonViOptions();

        return response()->json([
            'success' => true,
            'data' => [
                'san_pham' => [
                    'id' => $sanPham->id,
                    'ten_san_pham' => $sanPham->ten_san_pham,
                    'so_luong' => $sanPham->so_luong,
                    'don_vi_co_ban' => $sanPham->don_vi_co_ban,
                ],
                'don_vi_list' => $options,
            ],
        ]);
    }
}
