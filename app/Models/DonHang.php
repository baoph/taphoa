<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonHang extends Model
{
    protected $table = 'don_hang';

    protected $fillable = [
        'san_pham_id',
        'ten_san_pham',
        'so_luong',
        'gia',
        'ngay_ban',
    ];

    protected $casts = [
        'gia' => 'decimal:0',
        'ngay_ban' => 'date',
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
    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }
}
