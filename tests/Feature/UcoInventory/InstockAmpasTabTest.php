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
}
