<?php

namespace App\Imports;

use App\Models\SanPham;
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
     * Map dữ liệu từ Excel vào Model
     */
    public function model(array $row)
    {
        // Xử lý format số tiền (loại bỏ dấu phẩy, chữ "d", khoảng trắng)
        $giaNhap = $this->cleanMoneyFormat($row['gia_nhap_vao'] ?? $row['gia_nhap'] ?? 0);
        $giaBan = $this->cleanMoneyFormat($row['gia_ban_ra'] ?? $row['gia_ban'] ?? 0);
        $giaBanLe = $this->cleanMoneyFormat($row['gia_ban_le'] ?? 0);

        // Xử lý số lượng
        $soLuong = $this->cleanNumberFormat($row['so_luong_hang'] ?? $row['so_luong'] ?? 0);
        $soLuongDonVi = $this->cleanNumberFormat($row['so_luong_don_vi'] ?? 0);

        // Tính toán tỉ số chuyển đổi
        $tiSoChuyenDoi = 1;
        if ($soLuong > 0 && $soLuongDonVi > 0) {
            $tiSoChuyenDoi = round($soLuongDonVi / $soLuong, 2);
        }

        // Lấy tên sản phẩm
        $tenSanPham = $row['hang'] ?? $row['ten_san_pham'] ?? '';

        // Lấy đơn vị tính
        $dv_nhap_hang = $row['don_vi'] ?? $row['don_vi_tinh'] ?? $row['dv_nhap_hang'] ?? '';

        // Lấy ghi chú
        $ghiChu = $row['ghi_chu'] ?? '';

        return new SanPham([
            'ten_san_pham' => trim($tenSanPham),
            'dv_nhap_hang' => trim($dv_nhap_hang),
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
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'hang' => 'required|string|max:255',
            'don_vi' => 'nullable|string|max:50',
            'gia_nhap_vao' => 'nullable',
            'gia_ban_ra' => 'nullable',
            'gia_ban_le' => 'nullable',
            'so_luong_hang' => 'nullable',
            'so_luong_don_vi' => 'nullable',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'hang.required' => 'Tên hàng không được để trống',
            'hang.max' => 'Tên hàng không được vượt quá 255 ký tự',
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
        return floatval($value);
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

        // Chuyển về số thập phân
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
