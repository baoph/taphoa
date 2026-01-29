<?php

namespace App\Imports;

use App\Models\SanPham;
use App\Models\DonViBan;
use App\Models\SanPhamDonVi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class SanPhamImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    /**
     * Cache danh sách đơn vị bán để tránh query nhiều lần
     */
    protected $donViBanCache = null;

    /**
     * Danh sách errors
     */
    protected $errors = [];

    /**
     * Số sản phẩm đã import thành công
     */
    protected $successCount = 0;

    /**
     * Import collection từ Excel
     * Xử lý cả san_pham và san_pham_don_vi
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 vì index 0-based và có heading row

            try {
                DB::beginTransaction();

                // Lấy tên sản phẩm
                $tenSanPham = $row['ten_san_pham'] ?? $row['hang'] ?? '';
                
                if (empty(trim($tenSanPham))) {
                    $this->errors[] = "Dòng {$rowNumber}: Tên sản phẩm không được để trống";
                    DB::rollBack();
                    continue;
                }

                // Xử lý format số tiền
                $giaNhap = $this->cleanMoneyFormat($row['gia_nhap'] ?? $row['gia_nhap_vao'] ?? 0);
                $giaBan = $this->cleanMoneyFormat($row['gia_ban'] ?? $row['gia_ban_ra'] ?? 0);
                $giaBanLe = $this->cleanMoneyFormat($row['gia_ban_le'] ?? 0);

                // Xử lý số lượng
                $soLuong = $this->cleanNumberFormat($row['so_luong'] ?? $row['so_luong_hang'] ?? 0);

                // Xử lý đơn vị cơ bản (đơn vị nhỏ nhất để bán lẻ)
                $donViCoBanText = trim($row['don_vi_co_ban'] ?? $row['don_vi_ban'] ?? '');
                $donViCoBanId = $this->lookupOrCreateDonViBan($donViCoBanText);

                // Xử lý đơn vị nhập hàng (đơn vị lớn - thùng, lốc, etc.)
                $donViNhapText = trim($row['don_vi_nhap'] ?? $row['don_vi_nhap_hang'] ?? $row['dv_nhap_hang'] ?? '');
                $dvNhapHangId = $this->lookupOrCreateDonViBan($donViNhapText);

                // Tỉ số chuyển đổi (số đơn vị cơ bản trong 1 đơn vị nhập)
                $tiSoChuyenDoi = $this->cleanNumberFormat($row['ti_so_chuyen_doi'] ?? $row['ti_le_quy_doi'] ?? 1);
                if ($tiSoChuyenDoi <= 0) {
                    $tiSoChuyenDoi = 1;
                }

                // Tính số lượng đơn vị (số lượng tồn kho theo đơn vị cơ bản)
                $soLuongDonVi = $soLuong * $tiSoChuyenDoi;

                // Lấy ghi chú
                $ghiChu = $row['ghi_chu'] ?? '';

                // Tạo sản phẩm
                $sanPham = SanPham::create([
                    'ten_san_pham' => trim($tenSanPham),
                    'dv_nhap_hang' => $dvNhapHangId,
                    'don_vi_co_ban' => $donViCoBanId,
                    'gia_nhap' => $giaNhap,
                    'gia_ban' => $giaBan,
                    'gia_ban_le' => $giaBanLe,
                    'so_luong' => $soLuong,
                    'ti_so_chuyen_doi' => $tiSoChuyenDoi,
                    'so_luong_don_vi' => $soLuongDonVi,
                    'ghi_chu' => trim($ghiChu),
                ]);

                // ========================================
                // TẠO CÁC ĐƠN VỊ BÁN TRONG san_pham_don_vi
                // ========================================

                // 1. Đơn vị cơ bản (đơn vị nhỏ nhất, ti_le_quy_doi = 1)
                if ($donViCoBanId) {
                    SanPhamDonVi::create([
                        'san_pham_id' => $sanPham->id,
                        'don_vi_ban_id' => $donViCoBanId,
                        'ti_le_quy_doi' => 1,
                        'gia_ban' => $giaBanLe > 0 ? $giaBanLe : $giaBan,
                    ]);
                }

                // 2. Đơn vị nhập hàng (đơn vị lớn - nếu khác đơn vị cơ bản)
                if ($dvNhapHangId && $dvNhapHangId != $donViCoBanId) {
                    SanPhamDonVi::create([
                        'san_pham_id' => $sanPham->id,
                        'don_vi_ban_id' => $dvNhapHangId,
                        'ti_le_quy_doi' => $tiSoChuyenDoi,
                        'gia_ban' => $giaBan,
                    ]);
                }

                // 3. Đơn vị trung gian (lốc - nếu có)
                $donViTrungText = trim($row['don_vi_trung'] ?? '');
                $giaBanTrung = $this->cleanMoneyFormat($row['gia_ban_trung'] ?? 0);
                $tiLeQuyDoiTrung = $this->cleanNumberFormat($row['ti_le_quy_doi_trung'] ?? 0);

                if (!empty($donViTrungText) && $tiLeQuyDoiTrung > 0) {
                    $donViTrungId = $this->lookupOrCreateDonViBan($donViTrungText);
                    if ($donViTrungId && $donViTrungId != $donViCoBanId && $donViTrungId != $dvNhapHangId) {
                        SanPhamDonVi::create([
                            'san_pham_id' => $sanPham->id,
                            'don_vi_ban_id' => $donViTrungId,
                            'ti_le_quy_doi' => $tiLeQuyDoiTrung,
                            'gia_ban' => $giaBanTrung > 0 ? $giaBanTrung : ($giaBanLe * $tiLeQuyDoiTrung),
                        ]);
                    }
                }

                // 4. Đơn vị phụ 1 (nếu có)
                $donViPhu1Text = trim($row['don_vi_phu_1'] ?? '');
                $giaBanPhu1 = $this->cleanMoneyFormat($row['gia_ban_phu_1'] ?? 0);
                $tiLeQuyDoiPhu1 = $this->cleanNumberFormat($row['ti_le_quy_doi_phu_1'] ?? 0);

                if (!empty($donViPhu1Text) && $tiLeQuyDoiPhu1 > 0 && $giaBanPhu1 > 0) {
                    $donViPhu1Id = $this->lookupOrCreateDonViBan($donViPhu1Text);
                    if ($donViPhu1Id) {
                        SanPhamDonVi::create([
                            'san_pham_id' => $sanPham->id,
                            'don_vi_ban_id' => $donViPhu1Id,
                            'ti_le_quy_doi' => $tiLeQuyDoiPhu1,
                            'gia_ban' => $giaBanPhu1,
                        ]);
                    }
                }

                // 5. Đơn vị phụ 2 (nếu có)
                $donViPhu2Text = trim($row['don_vi_phu_2'] ?? '');
                $giaBanPhu2 = $this->cleanMoneyFormat($row['gia_ban_phu_2'] ?? 0);
                $tiLeQuyDoiPhu2 = $this->cleanNumberFormat($row['ti_le_quy_doi_phu_2'] ?? 0);

                if (!empty($donViPhu2Text) && $tiLeQuyDoiPhu2 > 0 && $giaBanPhu2 > 0) {
                    $donViPhu2Id = $this->lookupOrCreateDonViBan($donViPhu2Text);
                    if ($donViPhu2Id) {
                        SanPhamDonVi::create([
                            'san_pham_id' => $sanPham->id,
                            'don_vi_ban_id' => $donViPhu2Id,
                            'ti_le_quy_doi' => $tiLeQuyDoiPhu2,
                            'gia_ban' => $giaBanPhu2,
                        ]);
                    }
                }

                DB::commit();
                $this->successCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->errors[] = "Dòng {$rowNumber}: " . $e->getMessage();
            }
        }
    }

    /**
     * Lookup đơn vị bán ID từ tên, tạo mới nếu chưa tồn tại
     */
    protected function lookupOrCreateDonViBan(?string $tenDonVi): ?int
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

        // Tạo mới đơn vị bán
        $donViBan = DonViBan::create([
            'ten_don_vi' => $tenDonVi,
            'mo_ta' => 'Tự động tạo từ import Excel',
        ]);

        // Cập nhật cache
        $this->donViBanCache->put($key, $donViBan);

        return $donViBan->id;
    }

    /**
     * Xử lý format số tiền (loại bỏ dấu phẩy, chữ "d", khoảng trắng)
     */
    private function cleanMoneyFormat($value): int
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
    private function cleanNumberFormat($value): float
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

    /**
     * Lấy danh sách lỗi
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Lấy số sản phẩm import thành công
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * Kiểm tra có lỗi không
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
