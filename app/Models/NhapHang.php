<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NhapHang extends Model
{
    use HasFactory;

    protected $table = 'nhap_hang';

    protected $fillable = [
        'san_pham_id',
        'ten_san_pham',
        'so_luong',
        'gia_nhap',
        'ngay_nhap',
        'don_vi_ban_id',
        'so_luong_quy_doi',
        'ghi_chu',
    ];

    protected $casts = [
        'gia_nhap' => 'decimal:0',
        'ngay_nhap' => 'date',
        'so_luong_quy_doi' => 'decimal:2',
    ];

    /**
     * Tính thành tiền
     */
    public function getThanhTienAttribute(): float
    {
        return $this->so_luong * $this->gia_nhap;
    }

    /**
     * Relationship: Nhập hàng thuộc về một sản phẩm
     */
    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }

    /**
     * Relationship: Nhập hàng thuộc về một đơn vị bán
     */
    public function donViBan(): BelongsTo
    {
        return $this->belongsTo(DonViBan::class, 'don_vi_ban_id');
    }
}
