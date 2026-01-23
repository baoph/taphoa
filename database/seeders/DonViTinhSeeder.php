<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DonViTinhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $donViTinhs = [
            'Lon',
            'Chai',
            'Thùng',
            'Bịch',
            'Can',
            'Gói',
            'Ly',
            'Tô',
            'Hộp',
            'Cây',
            'Lốc',
            'Cái',
            'Cục',
            'Cặp',
        ];

        foreach ($donViTinhs as $donVi) {
            DB::table('don_vi_tinh')->insert([
                'ten_don_vi' => $donVi,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
