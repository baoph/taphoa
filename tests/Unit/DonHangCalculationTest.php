<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\SanPham;
use App\Models\DonViBan;
use App\Models\SanPhamDonVi;

class DonHangCalculationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_quy_doi_correctly_for_thung()
    {
        $sanPham = SanPham::factory()->create();
        $donViBan = DonViBan::factory()->create(['ten_don_vi' => 'Thùng']);

        $sanPhamDonVi = SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViBan->id,
            'ti_le_quy_doi' => 24,
            'gia_ban' => 160000,
        ]);

        $soLuongQuyDoi = $sanPhamDonVi->tinhSoLuongQuyDoi(2);

        $this->assertEquals(48, $soLuongQuyDoi); // 2 thùng * 24 lon
    }

    /** @test */
    public function it_calculates_quy_doi_correctly_for_loc()
    {
        $sanPham = SanPham::factory()->create();
        $donViBan = DonViBan::factory()->create(['ten_don_vi' => 'Lốc']);

        $sanPhamDonVi = SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViBan->id,
            'ti_le_quy_doi' => 6,
            'gia_ban' => 40000,
        ]);

        $soLuongQuyDoi = $sanPhamDonVi->tinhSoLuongQuyDoi(5);

        $this->assertEquals(30, $soLuongQuyDoi); // 5 lốc * 6 lon
    }

    /** @test */
    public function it_calculates_quy_doi_correctly_for_lon()
    {
        $sanPham = SanPham::factory()->create();
        $donViBan = DonViBan::factory()->create(['ten_don_vi' => 'Lon']);

        $sanPhamDonVi = SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViBan->id,
            'ti_le_quy_doi' => 1,
            'gia_ban' => 7000,
        ]);

        $soLuongQuyDoi = $sanPhamDonVi->tinhSoLuongQuyDoi(10);

        $this->assertEquals(10, $soLuongQuyDoi); // 10 lon * 1
    }

    /** @test */
    public function it_calculates_quy_doi_with_decimal_quantities()
    {
        $sanPham = SanPham::factory()->create();
        $donViBan = DonViBan::factory()->create(['ten_don_vi' => 'Thùng']);

        $sanPhamDonVi = SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViBan->id,
            'ti_le_quy_doi' => 24,
            'gia_ban' => 160000,
        ]);

        $soLuongQuyDoi = $sanPhamDonVi->tinhSoLuongQuyDoi(1.5);

        $this->assertEquals(36, $soLuongQuyDoi); // 1.5 thùng * 24 lon
    }

    /** @test */
    public function it_has_correct_price_for_each_unit()
    {
        $sanPham = SanPham::factory()->create();
        $donViThung = DonViBan::factory()->create(['ten_don_vi' => 'Thùng']);
        $donViLoc = DonViBan::factory()->create(['ten_don_vi' => 'Lốc']);
        $donViLon = DonViBan::factory()->create(['ten_don_vi' => 'Lon']);

        $spThung = SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViThung->id,
            'ti_le_quy_doi' => 24,
            'gia_ban' => 160000,
        ]);

        $spLoc = SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViLoc->id,
            'ti_le_quy_doi' => 6,
            'gia_ban' => 40000,
        ]);

        $spLon = SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViLon->id,
            'ti_le_quy_doi' => 1,
            'gia_ban' => 7000,
        ]);

        $this->assertEquals(160000, $spThung->gia_ban);
        $this->assertEquals(40000, $spLoc->gia_ban);
        $this->assertEquals(7000, $spLon->gia_ban);

        // Kiểm tra giá trung bình mỗi lon
        $this->assertEquals(6666.67, round($spThung->gia_ban / $spThung->ti_le_quy_doi, 2));
        $this->assertEquals(6666.67, round($spLoc->gia_ban / $spLoc->ti_le_quy_doi, 2));
        $this->assertEquals(7000, $spLon->gia_ban / $spLon->ti_le_quy_doi);
    }
}
