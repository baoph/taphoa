<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\SanPham;
use App\Models\DonViBan;
use App\Models\SanPhamDonVi;

class SanPhamDonViTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_add_don_vi_to_san_pham()
    {
        $sanPham = SanPham::factory()->create();
        $donViBan = DonViBan::factory()->create(['ten_don_vi' => 'Thùng']);

        $data = [
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViBan->id,
            'ti_le_quy_doi' => 24,
            'gia_ban' => 160000,
        ];

        $response = $this->postJson(route('san-pham-don-vi.store'), $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('san_pham_don_vi', [
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViBan->id,
            'ti_le_quy_doi' => 24,
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_don_vi_for_same_san_pham()
    {
        $sanPham = SanPham::factory()->create();
        $donViBan = DonViBan::factory()->create();

        SanPhamDonVi::factory()->create([
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViBan->id,
        ]);

        $data = [
            'san_pham_id' => $sanPham->id,
            'don_vi_ban_id' => $donViBan->id,
            'ti_le_quy_doi' => 12,
            'gia_ban' => 80000,
        ];

        $response = $this->postJson(route('san-pham-don-vi.store'), $data);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    /** @test */
    public function it_can_update_san_pham_don_vi()
    {
        $sanPhamDonVi = SanPhamDonVi::factory()->create([
            'ti_le_quy_doi' => 6,
            'gia_ban' => 40000,
        ]);

        $data = [
            'ti_le_quy_doi' => 12,
            'gia_ban' => 80000,
        ];

        $response = $this->putJson(route('san-pham-don-vi.update', $sanPhamDonVi), $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('san_pham_don_vi', [
            'id' => $sanPhamDonVi->id,
            'ti_le_quy_doi' => 12,
            'gia_ban' => 80000,
        ]);
    }

    /** @test */
    public function it_can_delete_san_pham_don_vi()
    {
        $sanPhamDonVi = SanPhamDonVi::factory()->create();

        $response = $this->deleteJson(route('san-pham-don-vi.destroy', $sanPhamDonVi));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('san_pham_don_vi', ['id' => $sanPhamDonVi->id]);
    }

    /** @test */
    public function it_can_get_don_vi_options_for_san_pham()
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

        $response = $this->getJson(route('san-pham.don-vi-options', $sanPham->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonCount(2, 'data');
    }
}
