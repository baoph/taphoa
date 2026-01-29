<?php

namespace App\Imports;

use App\Models\SanPham;
use App\Models\DonViBan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class SanPhamImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnError
{
    use SkipsErrors;

    /**
     * Cache danh sách đơn vị bán để tránh query nhiều lần
     */
    protected $donViBanCache = null;

    /**
     * Map dữ liệu từ Excel vào Model
     */
    public function model(array $row)
    {
        // Lấy tên sản phẩm
        $tenSanPham = $row['ten_san_pham'] ?? $row['hang'] ?? '';

        // Xử lý format số tiền
        $giaNhap = $this->cleanMoneyFormat($row['gia_nhap'] ?? $row['gia_nhap_vao'] ?? 0);
        $giaBan = $this->cleanMoneyFormat($row['gia_ban'] ?? $row['gia_ban_ra'] ?? 0);
        $giaBanLe = $this->cleanMoneyFormat($row['gia_ban_le'] ?? 0);

        // Xử lý số lượng
        $soLuong = $this->cleanNumberFormat($row['so_luong'] ?? $row['so_luong_hang'] ?? 0);

        // Xử lý đơn vị bán (đơn vị cơ bản - đơn vị nhỏ nhất để bán lẻ)
        $donViBanText = trim($row['don_vi_ban'] ?? $row['don_vi_co_ban'] ?? '');
        $donViCoBan = $this->lookupDonViBanId($donViBanText);

        // Xử lý đơn vị nhập hàng (đơn vị lớn - thùng, lốc, etc.)
        $donViNhapText = trim($row['don_vi_nhap'] ?? $row['don_vi_nhap_hang'] ?? $row['dv_nhap_hang'] ?? '');
        $dvNhapHang = $this->lookupDonViBanId($donViNhapText);

        // Tỉ số chuyển đổi (số đơn vị bán trong 1 đơn vị nhập)
        $tiSoChuyenDoi = $this->cleanNumberFormat($row['ti_so_chuyen_doi'] ?? $row['ti_le_quy_doi'] ?? 1);
        if ($tiSoChuyenDoi <= 0) {
            $tiSoChuyenDoi = 1;
        }

        // Tính số lượng đơn vị (số lượng tồn kho theo đơn vị cơ bản)
        $soLuongDonVi = $soLuong * $tiSoChuyenDoi;

        // Lấy ghi chú
        $ghiChu = $row['ghi_chu'] ?? '';

        return new SanPham([
            'ten_san_pham' => trim($tenSanPham),
            'dv_nhap_hang' => $dvNhapHang, // ID của đơn vị nhập hàng
            'don_vi_co_ban' => $donViCoBan, // ID hoặc tên của đơn vị bán cơ bản
            'gia_nhap' => $giaNhap,
            'gia_ban' => $giaBan,
            'gia_ban_le' => $giaBanLe,
            'so_luong' => $soLuong,
            'ti_so_chuyen_doi' => $tiSoChuyenDoi,
            'so_luong_don_vi' => $soLuongDonVi,
            'ghi_chu' => trim($ghiChu),
        ]);
    }

    /**
     * Lookup đơn vị bán ID từ tên
     * Trả về ID nếu tìm thấy, hoặc tên gốc nếu không tìm thấy
     */
    protected function lookupDonViBanId(string $tenDonVi)
    {
        if (empty($tenDonVi)) {
            return null;
        }

        // Load cache nếu chưa có
        if ($this->donViBanCache === null) {
            $this->donViBanCache = DonViBan::all()->keyBy(function ($item) {
                return mb_strtolower($item->ten_don_vi);
            });
        }

        // Tìm kiếm theo tên (case-insensitive)
        $key = mb_strtolower(trim($tenDonVi));
        if ($this->donViBanCache->has($key)) {
            return $this->donViBanCache->get($key)->id;
        }

        // Nếu không tìm thấy, trả về null
        // Có thể tạo mới đơn vị nếu cần
        return $this->createNewDonViBan($tenDonVi);
    }

    /**
     * Tạo mới đơn vị bán nếu chưa tồn tại
     */
    protected function createNewDonViBan(string $tenDonVi)
    {
        if (empty($tenDonVi)) {
            return null;
        }

        // Tạo mới đơn vị bán
        $donViBan = DonViBan::create([
            'ten_don_vi' => $tenDonVi,
            'mo_ta' => 'Tự động tạo từ import Excel',
        ]);

        // Cập nhật cache
        if ($this->donViBanCache !== null) {
            $this->donViBanCache->put(mb_strtolower($tenDonVi), $donViBan);
        }

        return $donViBan->id;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'ten_san_pham' => 'required|string|max:255',
            'don_vi_ban' => 'nullable|string|max:50',
            'don_vi_nhap' => 'nullable|string|max:50',
            'gia_nhap' => 'nullable',
            'gia_ban' => 'nullable',
            'gia_ban_le' => 'nullable',
            'so_luong' => 'nullable',
            'ti_so_chuyen_doi' => 'nullable',
            'ghi_chu' => 'nullable|string',
        ];
    }

    /**
     * Prepare data for validation (xử lý alias headers)
     */
    public function prepareForValidation($data, $index)
    {
        // Alias cho tên sản phẩm
        if (!isset($data['ten_san_pham']) && isset($data['hang'])) {
            $data['ten_san_pham'] = $data['hang'];
        }

        // Alias cho đơn vị bán
        if (!isset($data['don_vi_ban']) && isset($data['don_vi_co_ban'])) {
            $data['don_vi_ban'] = $data['don_vi_co_ban'];
        }

        // Alias cho đơn vị nhập
        if (!isset($data['don_vi_nhap'])) {
            $data['don_vi_nhap'] = $data['don_vi_nhap_hang'] ?? $data['dv_nhap_hang'] ?? null;
        }

        // Alias cho giá nhập
        if (!isset($data['gia_nhap']) && isset($data['gia_nhap_vao'])) {
            $data['gia_nhap'] = $data['gia_nhap_vao'];
        }

        // Alias cho giá bán
        if (!isset($data['gia_ban']) && isset($data['gia_ban_ra'])) {
            $data['gia_ban'] = $data['gia_ban_ra'];
        }

        // Alias cho số lượng
        if (!isset($data['so_luong']) && isset($data['so_luong_hang'])) {
            $data['so_luong'] = $data['so_luong_hang'];
        }

        // Alias cho tỉ số chuyển đổi
        if (!isset($data['ti_so_chuyen_doi']) && isset($data['ti_le_quy_doi'])) {
            $data['ti_so_chuyen_doi'] = $data['ti_le_quy_doi'];
        }

        return $data;
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'ten_san_pham.required' => 'Tên sản phẩm không được để trống',
            'ten_san_pham.max' => 'Tên sản phẩm không được vượt quá 255 ký tự',
            'don_vi_ban.max' => 'Đơn vị bán không được vượt quá 50 ký tự',
            'don_vi_nhap.max' => 'Đơn vị nhập không được vượt quá 50 ký tự',
        ];
    }

    /**
     * Xử lý format số tiền (loại bỏ dấu phẩy, chữ "d", khoảng trắng)
     */
    private function cleanMoneyFormat($value)
    {
        if (empty($value)) {
            return 0;
        }

        // Chuyển về string
        $value = (string) $value;

        // Loại bỏ dấu phẩy, chữ "d", "đ", khoảng trắng, ký tự đặc biệt
        $value = str_replace([',', '.', 'd', 'đ', 'D', 'Đ', ' ', 'VND', 'vnd'], '', $value);

        // Chuyển về số
        return intval($value);
    }

    /**
     * Xử lý format số (loại bỏ dấu phẩy)
     */
    private function cleanNumberFormat($value)
    {
        if (empty($value)) {
            return 0;
        }

        // Chuyển về string
        $value = (string) $value;

        // Loại bỏ dấu phẩy ngăn cách hàng nghìn
        $value = str_replace(',', '', $value);

        // Chuyển về số
        return floatval($value);
    }

    /**
     * Heading row index (dòng tiêu đề)
     */
    public function headingRow(): int
    {
        return 1;
    }
}
