<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SanPhamDonVi extends Model
{
    use HasFactory;

    protected $table = 'san_pham_don_vi';

    protected $fillable = [
        'san_pham_id',
        'don_vi_ban_id',
        'ti_le_quy_doi',
        'gia_ban',
    ];

    protected $casts = [
        'ti_le_quy_doi' => 'decimal:2',
        'gia_ban' => 'decimal:2',
    ];

    /**
     * Thuộc về sản phẩm
     */
    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }

    /**
     * Thuộc về đơn vị bán
     */
    public function donViBan(): BelongsTo
    {
        return $this->belongsTo(DonViBan::class, 'don_vi_ban_id');
    }

    /**
     * Tính số lượng quy đổi về đơn vị cơ bản
     */
    public function tinhSoLuongQuyDoi(float $soLuong): float
    {
        return $soLuong * $this->ti_le_quy_doi;
    }
}
