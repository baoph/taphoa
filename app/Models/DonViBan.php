<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonViBan extends Model
{
    use HasFactory;

    protected $table = 'don_vi_ban';

    protected $fillable = [
        'ten_don_vi',
        'mo_ta',
    ];

    /**
     * Một đơn vị bán có nhiều sản phẩm đơn vị
     */
    public function sanPhamDonVi(): HasMany
    {
        return $this->hasMany(SanPhamDonVi::class, 'don_vi_ban_id');
    }

    /**
     * Một đơn vị bán có nhiều đơn hàng
     */
    public function donHang(): HasMany
    {
        return $this->hasMany(DonHang::class, 'don_vi_ban_id');
    }
}
