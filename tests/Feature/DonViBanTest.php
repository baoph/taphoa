<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\DonViBan;

class DonViBanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_don_vi_ban()
    {
        DonViBan::factory()->count(5)->create();

        $response = $this->get(route('don-vi-ban.index'));

        $response->assertStatus(200);
        $response->assertViewIs('don-vi-ban.index');
        $response->assertViewHas('donViBans');
    }

    /** @test */
    public function it_can_create_don_vi_ban()
    {
        $data = [
            'ten_don_vi' => 'Thùng Test',
            'mo_ta' => 'Đơn vị test',
        ];

        $response = $this->post(route('don-vi-ban.store'), $data);

        $response->assertRedirect(route('don-vi-ban.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('don_vi_ban', ['ten_don_vi' => 'Thùng Test']);
    }

    /** @test */
    public function it_validates_required_fields_when_creating()
    {
        $response = $this->post(route('don-vi-ban.store'), []);

        $response->assertSessionHasErrors(['ten_don_vi']);
    }

    /** @test */
    public function it_can_update_don_vi_ban()
    {
        $donViBan = DonViBan::factory()->create(['ten_don_vi' => 'Old Name']);

        $data = [
            'ten_don_vi' => 'New Name',
            'mo_ta' => 'Updated description',
        ];

        $response = $this->put(route('don-vi-ban.update', $donViBan), $data);

        $response->assertRedirect(route('don-vi-ban.index'));
        $this->assertDatabaseHas('don_vi_ban', ['ten_don_vi' => 'New Name']);
    }

    /** @test */
    public function it_can_delete_don_vi_ban()
    {
        $donViBan = DonViBan::factory()->create();

        $response = $this->delete(route('don-vi-ban.destroy', $donViBan));

        $response->assertRedirect(route('don-vi-ban.index'));
        $this->assertDatabaseMissing('don_vi_ban', ['id' => $donViBan->id]);
    }
}
