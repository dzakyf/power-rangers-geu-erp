<?php

namespace Tests\Feature\UcoInventory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InstockAmpasTabTest extends TestCase
{
    use DatabaseTransactions;

    private function admin()
    {
        return \App\User::where('username', 'admin')->firstOrFail();
    }

    /** @test */
    public function the_ampas_tab_uses_bootstrap_5_tab_markup(): void
    {
        $response = $this->actingAs($this->admin())->get('/partner-uco-instock');

        $response->assertOk();
        $response->assertSee('data-bs-toggle="tab"', false);
        $response->assertSee('data-bs-target="#ampasTabContent"', false);
    }

    /** @test */
    public function the_ampas_tab_resizes_its_grid_and_uses_unique_webcam_ids(): void
    {
        $response = $this->actingAs($this->admin())->get('/partner-uco-instock');

        $response->assertOk();
        $response->assertSee("$('#ampas-tab').on('shown.bs.tab'", false);
        $response->assertSee("DregsGrid.setSizes()", false);
        $response->assertSee('id="dregsWebcam"', false);
        $response->assertSee('id="instockCreateWebcam"', false);
        $response->assertSee('id="instockUpdateWebcam"', false);
        $response->assertDontSee('id="webcam"', false);
    }
}
