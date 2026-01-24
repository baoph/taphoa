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
        'ngay_ban',
        'don_vi_ban_id',
        'so_luong_quy_doi',
    ];

    protected $casts = [
        'gia' => 'decimal:0',
        'ngay_ban' => 'date',
        'so_luong_quy_doi' => 'decimal:2',
    ];

    /**
     * Tính thành tiền
     */
    public function getThanhTienAttribute(): float
    {
        return $this->so_luong * $this->gia;
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
    public function donViBan(): BelongsTo
    {
        return $this->belongsTo(DonViBan::class, 'don_vi_ban_id');
    }
}
