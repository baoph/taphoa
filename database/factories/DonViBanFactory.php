<?php

namespace Database\Factories;

use App\Models\DonViBan;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonViBanFactory extends Factory
{
    protected $model = DonViBan::class;

    public function definition(): array
    {
        return [
            'ten_don_vi' => $this->faker->randomElement(['Thùng', 'Lốc', 'Lon', 'Chai', 'Bịch', 'Gói', 'Cái']),
            'mo_ta' => $this->faker->sentence(),
        ];
    }
}
