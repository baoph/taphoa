<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SanPham;
use App\Models\DonViBan;
use Carbon\Carbon;

class SanPhamDonViSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo sản phẩm mẫu: Bia Tiger
        $sanPham = SanPham::firstOrCreate(
            ['ten_san_pham' => 'Bia Tiger'],
            [
                'dvt' => 'Lon',
                'gia_nhap' => 6000,
                'gia_ban' => 7000,
                'gia_ban_le' => 7000,
                'so_luong' => 0,
                'don_vi_co_ban' => 'Lon',
                'so_luong_ton_kho' => 500, // 500 lon trong kho
                'ghi_chu' => 'Bia Tiger 330ml',
            ]
        );

        // Lấy các đơn vị bán
        $donViThung = DonViBan::where('ten_don_vi', 'Thùng')->first();
        $donViLoc = DonViBan::where('ten_don_vi', 'Lốc')->first();
        $donViLon = DonViBan::where('ten_don_vi', 'Lon')->first();

        $now = Carbon::now();
        $sanPhamDonViList = [
            [
                'san_pham_id' => $sanPham->id,
                'don_vi_ban_id' => $donViThung->id,
                'ti_le_quy_doi' => 24, // 1 thùng = 24 lon
                'gia_ban' => 160000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'san_pham_id' => $sanPham->id,
                'don_vi_ban_id' => $donViLoc->id,
                'ti_le_quy_doi' => 6, // 1 lốc = 6 lon
                'gia_ban' => 40000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'san_pham_id' => $sanPham->id,
                'don_vi_ban_id' => $donViLon->id,
                'ti_le_quy_doi' => 1, // 1 lon = 1 lon
                'gia_ban' => 7000,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('san_pham_don_vi')->insert($sanPhamDonViList);
    }
}
