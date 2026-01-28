<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SanPham extends Model
{
    use HasFactory;
    protected $table = 'san_pham';

    protected $fillable = [
        'ten_san_pham',
        'dv_nhap_hang',
        'gia_nhap',
        'gia_ban',
        'gia_ban_le',
        'so_luong',
        'ti_so_chuyen_doi',
        'so_luong_don_vi',
        'ghi_chu',
        'don_vi_co_ban',
        'so_luong',
    ];

    protected $casts = [
        'gia_nhap' => 'integer',
        'gia_ban' => 'integer',
        'gia_ban_le' => 'integer',
        'so_luong' => 'integer',
        'ti_so_chuyen_doi' => 'integer',
        'so_luong_don_vi' => 'integer',
    ];

    /**
     * Relationship: Sản phẩm có nhiều đơn hàng
     */
    public function donHangs(): HasMany
    {
        return $this->hasMany(DonHang::class, 'san_pham_id');
    }

    /**
     * Relationship: Sản phẩm có nhiều đơn vị bán
     */
    public function sanPhamDonVi(): HasMany
    {
        return $this->hasMany(SanPhamDonVi::class, 'san_pham_id');
    }

    public function donViBan(): HasMany
    {
        return $this->hasMany(DonViBan::class, 'id', 'dv_nhap_hang');
    }
    public function donViCoBan(): HasMany
    {
        return $this->hasMany(DonViBan::class, 'id', 'don_vi_co_ban');
    }

    public function getDvNhapHangTextAttribute()
    {
        $donVi = DonViBan::find($this->dv_nhap_hang);
        return $donVi ? $donVi->ten_don_vi : '';
    }
    public function getDonViCoBanTextAttribute()
    {
        $donVi = DonViBan::find($this->don_vi_co_ban);
        return $donVi ? $donVi->ten_don_vi : '';
    }

    public function getTonKhoHienThiAttribute()
    {
        $tongSoLuong = $this->so_luong ?? 0; // tồn kho theo đơn vị cơ bản

        // đơn vị nhập hàng
        $donViNhap = $this->sanPhamDonVi
            ->where('don_vi_ban_id', $this->dv_nhap_hang)
            ->first();
        if (!$donViNhap || $donViNhap->ti_le_quy_doi <= 0) {
            return number_format($tongSoLuong, 0, ',', '.') . ' ' . $this->don_vi_co_ban;
        }
        $tiSo = $donViNhap->ti_le_quy_doi;

        $soLuongNhap = intdiv($tongSoLuong, $tiSo);
        $soLuongLe   = $tongSoLuong % $tiSo;
        $donViNhapTen = $this->donViBan()->where('id', $this->dv_nhap_hang)->first()->ten_don_vi;
        $donViCoBanTen = $this->donViCoBan()->where('id', $this->don_vi_co_ban)->first()->ten_don_vi;
        $ketQua = [];
        if ($soLuongNhap > 0) {
            $ketQua[] = $soLuongNhap . ' ' . $donViNhapTen;
        }

        if ($soLuongLe > 0) {
            $ketQua[] = $soLuongLe . ' ' . $donViCoBanTen;
        }

        return implode(' ', $ketQua);
    }

    /**
     * Lấy danh sách đơn vị bán của sản phẩm
     */
    public function getDonViOptions(): array
    {
        return $this->sanPhamDonVi()
            ->with('donViBan')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'don_vi_ban_id' => $item->don_vi_ban_id,
                    'ten_don_vi' => $item->donViBan->ten_don_vi,
                    'ti_le_quy_doi' => $item->ti_le_quy_doi,
                    'gia_ban' => $item->gia_ban,
                ];
            })
            ->toArray();
    }

    /**
     * Lấy tồn kho theo đơn vị cơ bản
     */
    public function getTonKhoTheoLon(): float
    {
        return (float) $this->so_luong;
    }

    /**
     * Trừ tồn kho
     */
    public function truTonKho(float $soLuong): bool
    {
        if ($this->so_luong >= $soLuong) {
            $this->so_luong -= $soLuong;
            return $this->save();
        }
        return false;
    }

    /**
     * Cộng tồn kho
     */
    public function congTonKho(float $soLuong): bool
    {
        $this->so_luong += $soLuong;
        return $this->save();
    }
}
