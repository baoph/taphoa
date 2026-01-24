<?php

namespace Database\Factories;

use App\Models\SanPhamDonVi;
use App\Models\SanPham;
use App\Models\DonViBan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SanPhamDonViFactory extends Factory
{
    protected $model = SanPhamDonVi::class;

    public function definition(): array
    {
        return [
            'san_pham_id' => SanPham::factory(),
            'don_vi_ban_id' => DonViBan::factory(),
            'ti_le_quy_doi' => $this->faker->randomElement([1, 6, 12, 24]),
            'gia_ban' => $this->faker->numberBetween(10000, 200000),
        ];
    }
}
