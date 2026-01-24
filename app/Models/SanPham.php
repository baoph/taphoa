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
        'dvt',
        'gia_nhap',
        'gia_ban',
        'gia_ban_le',
        'so_luong',
        'ti_so_chuyen_doi',
        'so_luong_don_vi',
        'ghi_chu',
        'don_vi_co_ban',
        'so_luong_ton_kho',
    ];

    protected $casts = [
        'gia_nhap' => 'decimal:0',
        'gia_ban' => 'decimal:0',
        'gia_ban_le' => 'decimal:0',
        'so_luong' => 'decimal:2',
        'ti_so_chuyen_doi' => 'decimal:2',
        'so_luong_don_vi' => 'decimal:2',
        'so_luong_ton_kho' => 'decimal:2',
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
        return (float) $this->so_luong_ton_kho;
    }

    /**
     * Trừ tồn kho
     */
    public function truTonKho(float $soLuong): bool
    {
        if ($this->so_luong_ton_kho >= $soLuong) {
            $this->so_luong_ton_kho -= $soLuong;
            return $this->save();
        }
        return false;
    }

    /**
     * Cộng tồn kho
     */
    public function congTonKho(float $soLuong): bool
    {
        $this->so_luong_ton_kho += $soLuong;
        return $this->save();
    }
}
