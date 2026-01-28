<?php

namespace App\Http\Controllers;

use App\Models\SanPham;
use App\Models\DonViTinh;
use Illuminate\Http\Request;
use App\Imports\SanPhamImport;
use Maatwebsite\Excel\Facades\Excel;

class SanPhamController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        
        $sanPhams = SanPham::when($search, function ($query, $search) {
                return $query->where('ten_san_pham', 'like', '%' . $search . '%');
            })
            ->orderBy('ten_san_pham')
            ->paginate(15);
        
        if ($request->ajax()) {
            return view('san-pham.partials.table', compact('sanPhams'))->render();
        }
        
        return view('san-pham.index', compact('sanPhams', 'search'));
    }

    /**
     * AJAX Search - Tìm kiếm sản phẩm real-time
     */
    public function searchAjax(Request $request)
    {
        $search = $request->get('search', '');
        
        $sanPhams = SanPham::when($search, function ($query, $search) {
                return $query->where('ten_san_pham', 'like', '%' . $search . '%');
            })
            ->orderBy('ten_san_pham')
            ->paginate(15);
        
        return response()->json([
            'success' => true,
            'html' => view('san-pham.partials.table', compact('sanPhams'))->render(),
            'pagination' => view('san-pham.partials.pagination', compact('sanPhams'))->render(),
        ]);
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
            'so_luong' => 'nullable|numeric|min:0',
            'ti_so_chuyen_doi' => 'nullable|numeric|min:0.01',
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
            'so_luong' => 'nullable|numeric|min:0',
            'ti_so_chuyen_doi' => 'nullable|numeric|min:0.01',
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

    /**
     * API: Lấy thông tin sản phẩm cho Select2 (bán hàng)
     */
    public function getSanPham(Request $request)
    {
        $search = $request->get('q', '');
        $sanPhams = SanPham::where('ten_san_pham', 'like', "%{$search}%")
            ->select('id', 'ten_san_pham', 'gia_ban', 'dvt')
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $sanPhams->map(function ($sp) {
                return [
                    'id' => $sp->id,
                    'text' => $sp->ten_san_pham,
                    'gia_ban' => $sp->gia_ban,
                    'dvt' => $sp->dvt,
                ];
            })
        ]);
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
            ->get(['id', 'ten_san_pham', 'gia_ban', 'dvt']);

        return response()->json([
            'results' => $sanPhams->map(function ($sp) {
                return [
                    'id' => $sp->id,
                    'text' => $sp->ten_san_pham,
                    'gia_ban' => $sp->gia_ban,
                    'dvt' => $sp->dvt,
                ];
            })
        ]);
    }

    /**
     * Hiển thị chi tiết sản phẩm
     */
    public function show(int $id)
    {
        $sanPham = SanPham::with('sanPhamDonVi.donViBan')->findOrFail($id);
        return view('san-pham.show', compact('sanPham'));
    }

    /**
     * API: Lấy danh sách đơn vị bán của sản phẩm
     * 
     * Response format:
     * {
     *   "success": true,
     *   "data": {
     *     "san_pham": {
     *       "id": 1,
     *       "ten_san_pham": "Bia Tiger",
     *       "don_vi_co_ban": "lon",
     *       "so_luong_ton_kho": 240
     *     },
     *     "don_vi_list": [
     *       {
     *         "id": 1,
     *         "don_vi_ban_id": 1,
     *         "ten_don_vi": "Thùng",
     *         "ti_le_quy_doi": 24,
     *         "gia_ban": 280000
     *       }
     *     ]
     *   }
     * }
     */
    public function getDonViOptions(int $id)
    {
        try {
            $sanPham = SanPham::findOrFail($id);
            $donViList = $sanPham->getDonViOptions();

            return response()->json([
                'success' => true,
                'data' => [
                    'san_pham' => [
                        'id' => $sanPham->id,
                        'ten_san_pham' => $sanPham->ten_san_pham,
                        'don_vi_co_ban' => $sanPham->don_vi_co_ban ?? $sanPham->dvt ?? 'cái',
                        'so_luong_ton_kho' => (float) ($sanPham->so_luong_ton_kho ?? 0),
                    ],
                    'don_vi_list' => $donViList,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm hoặc có lỗi xảy ra: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Hiển thị form import Excel
     */
    public function showImportForm()
    {
        return view('san-pham.import');
    }

    /**
     * Xử lý import Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120', // Max 5MB
        ], [
            'file.required' => 'Vui lòng chọn file Excel để import',
            'file.mimes' => 'File phải có định dạng: xlsx, xls hoặc csv',
            'file.max' => 'Kích thước file không được vượt quá 5MB',
        ]);

        try {
            $file = $request->file('file');
            
            // Import dữ liệu
            $import = new SanPhamImport();
            Excel::import($import, $file);
            
            // Kiểm tra lỗi
            $errors = $import->errors();
            
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = "Dòng {$error->row()}: {$error->errors()[0]}";
                }
                
                return redirect()->back()
                    ->with('warning', 'Import hoàn tất nhưng có một số lỗi:')
                    ->with('errors', $errorMessages);
            }
            
            return redirect()->route('san-pham.index')
                ->with('success', 'Import dữ liệu thành công!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi import: ' . $e->getMessage());
        }
    }
}
