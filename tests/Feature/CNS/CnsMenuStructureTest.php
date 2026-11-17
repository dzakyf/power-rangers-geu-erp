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
        $this->assertNull(
            DB::table('menu_lists')->where('Menu_ID', 'SI00334')->first(),
            'Legacy Master User (SI00334) must be removed in favour of the CNS access-control menus'
        );

        $userManagement = DB::table('menu_lists')->where('Menu_ID', 'SI00347')->first();
        $this->assertNotNull($userManagement, 'Manajemen Pengguna (SI00347) must exist');
        $this->assertSame('SS00095', $userManagement->Parent_Menu);

        $roleManagement = DB::table('menu_lists')->where('Menu_ID', 'SI00348')->first();
        $this->assertNotNull($roleManagement, 'Role & Hak Akses (SI00348) must exist');
        $this->assertSame('SS00095', $roleManagement->Parent_Menu);

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
        $response->assertSee('Manajemen Pengguna', false);
        $response->assertSee('Role & Hak Akses', false);
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
        $this->assertArrayNotHasKey('SI00334', $snapshot, 'Legacy Master User SI00334 is replaced by the access-control menus');
        $this->assertArrayHasKey('SI00347', $snapshot, 'Manajemen Pengguna must use SI00347');
        $this->assertSame('SS00095', $snapshot['SI00347'][4]);
        $this->assertArrayHasKey('SI00348', $snapshot, 'Role & Hak Akses must use SI00348');
        $this->assertSame('SS00095', $snapshot['SI00348'][4]);

        $this->assertArrayHasKey('SI00332', $snapshot, 'Inventory Non-UCO must use the CNS SI00332 id');
        $this->assertSame('Inventory Non-UCO', $snapshot['SI00332'][0]);
        $this->assertSame('SS00091', $snapshot['SI00332'][4]);
    }
}
