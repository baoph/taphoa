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
    ];

    protected $casts = [
        'gia_nhap' => 'decimal:0',
        'gia_ban' => 'decimal:0',
        'gia_ban_le' => 'decimal:0',
    ];
}
