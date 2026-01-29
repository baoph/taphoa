<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonHang extends Model
{
    use HasFactory;
    protected $table = 'don_hang';

    protected $fillable = [
        'san_pham_id',
        'ten_san_pham',
        'so_luong',
        'gia',
        'gia_nhap',  // ← THÊM MỚI: Giá vốn
        'ngay_ban',
        'don_vi_ban_id',
        'so_luong_quy_doi',
    ];

    protected $casts = [
        'gia' => 'decimal:0',
        'gia_nhap' => 'decimal:0',  // ← THÊM MỚI
        'ngay_ban' => 'date',
        'so_luong_quy_doi' => 'decimal:2',
    ];

    /**
     * Tính thành tiền (doanh thu)
     */
    public function getThanhTienAttribute(): float
    {
        return $this->so_luong * $this->gia;
    }

    /**
     * Tính tổng giá vốn
     */
    public function getTongGiaVonAttribute(): float
    {
        return $this->so_luong * $this->gia_nhap;
    }

    /**
     * Tính lợi nhuận
     * Công thức: Lợi nhuận = Thành tiền - Tổng giá vốn
     */
    public function getLoiNhuanAttribute(): float
    {
        return $this->thanh_tien - $this->tong_gia_von;
    }

    /**
     * Tính tỷ suất lợi nhuận (%)
     * Công thức: (Lợi nhuận / Tổng giá vốn) × 100
     */
    public function getTySuatLoiNhuanAttribute(): float
    {
        if ($this->tong_gia_von == 0) {
            return 0;
        }
        return ($this->loi_nhuan / $this->tong_gia_von) * 100;
    }

    /**
     * Tính tỷ lệ lợi nhuận trên doanh thu (%)
     * Công thức: (Lợi nhuận / Thành tiền) × 100
     */
    public function getTyLeLoiNhuanAttribute(): float
    {
        if ($this->thanh_tien == 0) {
            return 0;
        }
        return ($this->loi_nhuan / $this->thanh_tien) * 100;
    }

    /**
     * Tính tổng lợi nhuận cho collection đơn hàng
     * Sử dụng trong Controller để tính tổng
     */
    public static function tinhTongLoiNhuan($donHangs)
    {
        $tongDoanhThu = $donHangs->sum(function ($item) {
            return $item->so_luong * $item->gia;
        });

        $tongGiaVon = $donHangs->sum(function ($item) {
            return $item->so_luong * $item->gia_nhap;
        });

        return [
            'tong_doanh_thu' => $tongDoanhThu,
            'tong_gia_von' => $tongGiaVon,
            'tong_loi_nhuan' => $tongDoanhThu - $tongGiaVon,
            'ty_le_loi_nhuan' => $tongDoanhThu > 0 ? (($tongDoanhThu - $tongGiaVon) / $tongDoanhThu) * 100 : 0,
        ];
    }

    /**
     * Relationship: Đơn hàng thuộc về một sản phẩm
     */
    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }

    /**
     * Relationship: Đơn hàng thuộc về một đơn vị bán
     */
    public function sanPhamDonVi(): BelongsTo
    {
        return $this->belongsTo(SanPhamDonVi::class, 'don_vi_ban_id');
    }

    /**
     * Relationship: Đơn hàng thuộc về một đơn vị bán
     */
    public function donViBan(): BelongsTo
    {
        return $this->belongsTo(DonViBan::class, 'don_vi_ban_id');
    }
}
