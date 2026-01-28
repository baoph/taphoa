<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\SanPham;
use App\Models\DonViBan;
use App\Models\SanPhamDonVi;

class SanPhamTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_san_pham_don_vi_relationship()
    {
        $sanPham = SanPham::factory()->create();
        $donViBan = DonViBan::factory()->create();

        SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViBan->id,
        ]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $sanPham->sanPhamDonVi);
        $this->assertCount(1, $sanPham->sanPhamDonVi);
    }

    /** @test */
    public function it_can_get_don_vi_options()
    {
        $sanPham = SanPham::factory()->create();
        $donViBan1 = DonViBan::factory()->create(['ten_don_vi' => 'Thùng']);
        $donViBan2 = DonViBan::factory()->create(['ten_don_vi' => 'Lốc']);

        SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViBan1->id,
            'ti_le_quy_doi' => 24,
            'gia_ban' => 160000,
        ]);

        SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViBan2->id,
            'ti_le_quy_doi' => 6,
            'gia_ban' => 40000,
        ]);

        $options = $sanPham->getDonViOptions();

        $this->assertIsArray($options);
        $this->assertCount(2, $options);
        $this->assertEquals('Thùng', $options[0]['ten_don_vi']);
        $this->assertEquals(24, $options[0]['ti_le_quy_doi']);
    }

    /** @test */
    public function it_can_get_ton_kho_theo_lon()
    {
        $sanPham = SanPham::factory()->create([
            'so_luong' => 500.50,
        ]);

        $tonKho = $sanPham->getTonKhoTheoLon();

        $this->assertEquals(500.50, $tonKho);
        $this->assertIsFloat($tonKho);
    }

    /** @test */
    public function it_can_tru_ton_kho()
    {
        $sanPham = SanPham::factory()->create([
            'so_luong' => 100,
        ]);

        $result = $sanPham->truTonKho(30);

        $this->assertTrue($result);
        $this->assertEquals(70, $sanPham->so_luong);
    }

    /** @test */
    public function it_prevents_tru_ton_kho_when_insufficient()
    {
        $sanPham = SanPham::factory()->create([
            'so_luong' => 20,
        ]);

        $result = $sanPham->truTonKho(50);

        $this->assertFalse($result);
        $this->assertEquals(20, $sanPham->so_luong);
    }

    /** @test */
    public function it_can_cong_ton_kho()
    {
        $sanPham = SanPham::factory()->create([
            'so_luong' => 100,
        ]);

        $result = $sanPham->congTonKho(50);

        $this->assertTrue($result);
        $this->assertEquals(150, $sanPham->so_luong);
    }
}
