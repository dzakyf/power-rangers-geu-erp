<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MasterConfigSystemPageTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_renders_the_config_system_page_inside_the_business_partner_theme(): void
    {
        $user = User::query()->where('Priv_ID', 'ADM')->orderBy('id')->first()
            ?: User::query()->orderBy('id')->first();

        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }

        $response = $this->actingAs($user)->get('/master-config-system');

        $response->assertOk();
        $response->assertSee('Pengaturan');
        $response->assertSee('General');
        $response->assertSee('System Settings');
    }
}
