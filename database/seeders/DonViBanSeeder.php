<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DonViBanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $donViList = [
            ['ten_don_vi' => 'Thùng', 'mo_ta' => 'Đơn vị bán theo thùng'],
            ['ten_don_vi' => 'Lốc', 'mo_ta' => 'Đơn vị bán theo lốc'],
            ['ten_don_vi' => 'Lon', 'mo_ta' => 'Đơn vị bán theo lon'],
            ['ten_don_vi' => 'Chai', 'mo_ta' => 'Đơn vị bán theo chai'],
            ['ten_don_vi' => 'Bịch', 'mo_ta' => 'Đơn vị bán theo bịch'],
            ['ten_don_vi' => 'Can', 'mo_ta' => 'Đơn vị bán theo can'],
            ['ten_don_vi' => 'Gói', 'mo_ta' => 'Đơn vị bán theo gói'],
            ['ten_don_vi' => 'Ly', 'mo_ta' => 'Đơn vị bán theo ly'],
            ['ten_don_vi' => 'Tô', 'mo_ta' => 'Đơn vị bán theo tô'],
            ['ten_don_vi' => 'Hộp', 'mo_ta' => 'Đơn vị bán theo hộp'],
            ['ten_don_vi' => 'Cây', 'mo_ta' => 'Đơn vị bán theo cây'],
            ['ten_don_vi' => 'Cái', 'mo_ta' => 'Đơn vị bán theo cái'],
            ['ten_don_vi' => 'Cục', 'mo_ta' => 'Đơn vị bán theo cục'],
            ['ten_don_vi' => 'Cặp', 'mo_ta' => 'Đơn vị bán theo cặp'],
        ];

        $now = Carbon::now();
        foreach ($donViList as &$donVi) {
            $donVi['created_at'] = $now;
            $donVi['updated_at'] = $now;
        }

        DB::table('don_vi_ban')->insert($donViList);
    }
}
