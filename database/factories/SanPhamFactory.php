<?php

namespace Database\Factories;

use App\Models\SanPham;
use Illuminate\Database\Eloquent\Factories\Factory;

class SanPhamFactory extends Factory
{
    protected $model = SanPham::class;

    public function definition(): array
    {
        return [
            'ten_san_pham' => $this->faker->words(3, true),
            'dv_nhap_hang' => $this->faker->randomElement(['Lon', 'Chai', 'Gói', 'Cái']),
            'gia_nhap' => $this->faker->numberBetween(5000, 50000),
            'gia_ban' => $this->faker->numberBetween(6000, 60000),
            'gia_ban_le' => $this->faker->numberBetween(7000, 70000),
            'so_luong' => $this->faker->randomFloat(2, 0, 100),
            'ti_so_chuyen_doi' => 1,
            'so_luong_don_vi' => 0,
            'don_vi_co_ban' => 'Cái',
            'so_luong' => $this->faker->randomFloat(2, 100, 1000),
            'ghi_chu' => $this->faker->optional()->sentence(),
        ];
    }
}
