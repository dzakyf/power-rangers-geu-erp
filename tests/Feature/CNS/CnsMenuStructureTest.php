<?php

namespace Tests\Feature\CNS;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CnsMenuStructureTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('Position', 'Administrator')->firstOrFail();
    }

    /** @test */
    public function master_user_and_inventory_menus_have_the_correct_hierarchy(): void
    {
        $masterUser = DB::table('menu_lists')->where('Menu_ID', 'SI00334')->first();
        $this->assertNotNull($masterUser, 'Master User menu (SI00334) must exist');
        $this->assertSame('CNS', $masterUser->Modules);
        $this->assertSame('SS00088', $masterUser->Parent_Menu, 'Master User must be under Master Data');

        $tankDraining = DB::table('menu_lists')->where('Menu_ID', 'SI00340')->first();
        $this->assertNotNull($tankDraining, 'Tank Draining menu (SI00340) must exist');
        $this->assertSame('Tank Draining', $tankDraining->Name);
        $this->assertSame('cns-tank-draining', $tankDraining->Path_Loc);
        $this->assertSame('CNS', $tankDraining->Modules);
        $this->assertSame('SS00087', $tankDraining->Parent_Menu, 'Tank Draining must be under Inventory UCO');

        $inventoryNonUco = DB::table('menu_lists')->where('Menu_ID', 'SI00332')->first();
        $this->assertNotNull($inventoryNonUco, 'Inventory Non-UCO menu (SI00332) must exist');
        $this->assertSame('SS00091', $inventoryNonUco->Parent_Menu, 'Inventory Non-UCO must be under Inventory Non UCO');

        $inventoryKemasan = DB::table('menu_lists')->where('Menu_ID', 'SI00341')->first();
        $this->assertNotNull($inventoryKemasan, 'Inventory Kemasan menu (SI00341) must exist');
        $this->assertSame('SS00091', $inventoryKemasan->Parent_Menu);
        $this->assertSame('CNS', $inventoryKemasan->Modules);
    }

    /** @test */
    public function cns_sidebar_renders_master_user_and_inventory_uco_children(): void
    {
        $response = $this->actingAs($this->admin())->get('/home-cns');

        $response->assertOk();
        $response->assertSee('Master User', false);
        $response->assertSee('Inventory UCO', false);
        $response->assertSee('Tank Draining', false);
        $response->assertSee('Inventory Non UCO', false);
        $response->assertSee('Inventory Kemasan', false);
        $response->assertSee('Inventory Non-UCO', false);
    }

    /** @test */
    public function cns_menu_snapshot_does_not_claim_menus_owned_by_other_modules(): void
    {
        $snapshot = require database_path('seeds/data/menu_lists_cns.php');

        $this->assertArrayNotHasKey('SI00338', $snapshot, 'SI00338 is owned by the Credential module');
        $this->assertArrayHasKey('SI00334', $snapshot, 'Master User must use SI00334');
        $this->assertSame('Master User', $snapshot['SI00334'][0]);
        $this->assertSame('SS00088', $snapshot['SI00334'][4]);

        $this->assertArrayHasKey('SI00332', $snapshot, 'Inventory Non-UCO must use the CNS SI00332 id');
        $this->assertSame('Inventory Non-UCO', $snapshot['SI00332'][0]);
        $this->assertSame('SS00091', $snapshot['SI00332'][4]);
    }
}
