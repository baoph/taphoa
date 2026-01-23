<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SanPham extends Model
{
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
    ];

    protected $casts = [
        'gia_nhap' => 'decimal:0',
        'gia_ban' => 'decimal:0',
        'gia_ban_le' => 'decimal:0',
        'so_luong' => 'integer',
        'ti_so_chuyen_doi' => 'integer',
        'so_luong_don_vi' => 'integer',
    ];
}
