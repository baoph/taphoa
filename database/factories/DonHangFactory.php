<?php

namespace Database\Factories;

use App\Models\DonHang;
use App\Models\SanPham;
use App\Models\DonViBan;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonHangFactory extends Factory
{
    protected $model = DonHang::class;

    public function definition(): array
    {
        return [
            'san_pham_id' => SanPham::factory(),
            'ten_san_pham' => $this->faker->words(3, true),
            'so_luong' => $this->faker->numberBetween(1, 10),
            'gia' => $this->faker->numberBetween(10000, 100000),
            'ngay_ban' => $this->faker->date(),
            'don_vi_ban_id' => null,
            'so_luong_quy_doi' => 0,
        ];
    }
}
