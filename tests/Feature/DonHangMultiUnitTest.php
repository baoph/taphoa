<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\SanPham;
use App\Models\DonViBan;
use App\Models\SanPhamDonVi;
use App\Models\DonHang;

class DonHangMultiUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_don_hang_with_multi_unit()
    {
        $sanPham = SanPham::factory()->create([
            'ten_san_pham' => 'Bia Tiger',
            'don_vi_co_ban' => 'Lon',
            'so_luong_ton_kho' => 500,
        ]);

        $donViBan = DonViBan::factory()->create(['ten_don_vi' => 'Thùng']);

        $sanPhamDonVi = SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViBan->id,
            'ti_le_quy_doi' => 24, // 1 thùng = 24 lon
            'gia_ban' => 160000,
        ]);

        $data = [
            'san_pham_id' => $sanPham->id,
            'ten_san_pham' => 'Bia Tiger',
            'so_luong' => 2, // 2 thùng
            'gia' => 160000,
            'ngay_ban' => now()->format('Y-m-d'),
            'don_vi_ban_id' => $donViBan->id,
        ];

        $response = $this->postJson(route('don-hang.store'), $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Kiểm tra đơn hàng được tạo
        $this->assertDatabaseHas('don_hang', [
            'san_pham_id' => $sanPham->id,
            'so_luong' => 2,
            'don_vi_ban_id' => $donViBan->id,
            'so_luong_quy_doi' => 48, // 2 thùng * 24 lon
        ]);

        // Kiểm tra tồn kho đã bị trừ đúng
        $sanPham->refresh();
        $this->assertEquals(452, $sanPham->so_luong_ton_kho); // 500 - 48 = 452
    }

    /** @test */
    public function it_prevents_creating_don_hang_when_insufficient_stock()
    {
        $sanPham = SanPham::factory()->create([
            'ten_san_pham' => 'Bia Tiger',
            'don_vi_co_ban' => 'Lon',
            'so_luong_ton_kho' => 20, // Chỉ còn 20 lon
        ]);

        $donViBan = DonViBan::factory()->create(['ten_don_vi' => 'Thùng']);

        SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViBan->id,
            'ti_le_quy_doi' => 24,
            'gia_ban' => 160000,
        ]);

        $data = [
            'san_pham_id' => $sanPham->id,
            'ten_san_pham' => 'Bia Tiger',
            'so_luong' => 2, // 2 thùng = 48 lon, nhưng chỉ còn 20 lon
            'gia' => 160000,
            'ngay_ban' => now()->format('Y-m-d'),
            'don_vi_ban_id' => $donViBan->id,
        ];

        $response = $this->postJson(route('don-hang.store'), $data);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);

        // Tồn kho không thay đổi
        $sanPham->refresh();
        $this->assertEquals(20, $sanPham->so_luong_ton_kho);
    }

    /** @test */
    public function it_can_update_don_hang_and_adjust_stock_correctly()
    {
        $sanPham = SanPham::factory()->create([
            'so_luong_ton_kho' => 500,
        ]);

        $donViBan = DonViBan::factory()->create(['ten_don_vi' => 'Lốc']);

        SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViBan->id,
            'ti_le_quy_doi' => 6,
            'gia_ban' => 40000,
        ]);

        // Tạo đơn hàng ban đầu: 5 lốc = 30 lon
        $donHang = DonHang::factory()->create([
            'san_pham_id' => $sanPham->id,
            'so_luong' => 5,
            'don_vi_ban_id' => $donViBan->id,
            'so_luong_quy_doi' => 30,
        ]);

        // Trừ tồn kho ban đầu
        $sanPham->truTonKho(30);
        $this->assertEquals(470, $sanPham->so_luong_ton_kho);

        // Cập nhật đơn hàng: 10 lốc = 60 lon
        $data = [
            'san_pham_id' => $sanPham->id,
            'ten_san_pham' => $sanPham->ten_san_pham,
            'so_luong' => 10,
            'gia' => 40000,
            'ngay_ban' => now()->format('Y-m-d'),
            'don_vi_ban_id' => $donViBan->id,
        ];

        $response = $this->putJson(route('don-hang.update', $donHang), $data);

        $response->assertStatus(200);

        // Kiểm tra tồn kho: 470 + 30 (hoàn lại) - 60 (trừ mới) = 440
        $sanPham->refresh();
        $this->assertEquals(440, $sanPham->so_luong_ton_kho);
    }

    /** @test */
    public function it_restores_stock_when_deleting_don_hang()
    {
        $sanPham = SanPham::factory()->create([
            'so_luong_ton_kho' => 500,
        ]);

        $donViBan = DonViBan::factory()->create(['ten_don_vi' => 'Thùng']);

        SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViBan->id,
            'ti_le_quy_doi' => 24,
            'gia_ban' => 160000,
        ]);

        // Tạo đơn hàng: 3 thùng = 72 lon
        $donHang = DonHang::factory()->create([
            'san_pham_id' => $sanPham->id,
            'so_luong' => 3,
            'don_vi_ban_id' => $donViBan->id,
            'so_luong_quy_doi' => 72,
        ]);

        // Trừ tồn kho
        $sanPham->truTonKho(72);
        $this->assertEquals(428, $sanPham->so_luong_ton_kho);

        // Xóa đơn hàng
        $response = $this->deleteJson(route('don-hang.destroy', $donHang));

        $response->assertStatus(200);

        // Kiểm tra tồn kho được hoàn lại: 428 + 72 = 500
        $sanPham->refresh();
        $this->assertEquals(500, $sanPham->so_luong_ton_kho);
    }

    /** @test */
    public function it_calculates_conversion_correctly_for_different_units()
    {
        $sanPham = SanPham::factory()->create([
            'so_luong_ton_kho' => 1000,
        ]);

        $donViThung = DonViBan::factory()->create(['ten_don_vi' => 'Thùng']);
        $donViLoc = DonViBan::factory()->create(['ten_don_vi' => 'Lốc']);
        $donViLon = DonViBan::factory()->create(['ten_don_vi' => 'Lon']);

        SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViThung->id,
            'ti_le_quy_doi' => 24,
            'gia_ban' => 160000,
        ]);

        SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViLoc->id,
            'ti_le_quy_doi' => 6,
            'gia_ban' => 40000,
        ]);

        SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViLon->id,
            'ti_le_quy_doi' => 1,
            'gia_ban' => 7000,
        ]);

        // Bán 1 thùng
        $this->postJson(route('don-hang.store'), [
            'san_pham_id' => $sanPham->id,
            'ten_san_pham' => $sanPham->ten_san_pham,
            'so_luong' => 1,
            'gia' => 160000,
            'ngay_ban' => now()->format('Y-m-d'),
            'don_vi_ban_id' => $donViThung->id,
        ]);

        $sanPham->refresh();
        $this->assertEquals(976, $sanPham->so_luong_ton_kho); // 1000 - 24

        // Bán 2 lốc
        $this->postJson(route('don-hang.store'), [
            'san_pham_id' => $sanPham->id,
            'ten_san_pham' => $sanPham->ten_san_pham,
            'so_luong' => 2,
            'gia' => 40000,
            'ngay_ban' => now()->format('Y-m-d'),
            'don_vi_ban_id' => $donViLoc->id,
        ]);

        $sanPham->refresh();
        $this->assertEquals(964, $sanPham->so_luong_ton_kho); // 976 - 12

        // Bán 5 lon
        $this->postJson(route('don-hang.store'), [
            'san_pham_id' => $sanPham->id,
            'ten_san_pham' => $sanPham->ten_san_pham,
            'so_luong' => 5,
            'gia' => 7000,
            'ngay_ban' => now()->format('Y-m-d'),
            'don_vi_ban_id' => $donViLon->id,
        ]);

        $sanPham->refresh();
        $this->assertEquals(959, $sanPham->so_luong_ton_kho); // 964 - 5
    }
}
