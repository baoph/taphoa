<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonViTinh extends Model
{
    protected $table = 'don_vi_tinh';

    protected $fillable = [
        'ten_don_vi',
    ];
}
