<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonHang extends Model
{
    protected $table = 'don_hang';

    protected $fillable = [
        'ten_san_pham',
        'so_luong',
        'gia',
        'ngay_ban',
    ];

    protected $casts = [
        'ngay_ban' => 'date',
    ];
}
