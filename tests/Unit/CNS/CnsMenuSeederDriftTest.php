<?php

namespace Tests\Unit\CNS;

use App\Http\Controllers\Credential\CredentialRoleController;
use App\Role;
use App\Services\CNS\CnsPermissionService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CnsMenuSeederDriftTest extends TestCase
{
    /** @test */
    public function cns_menu_snapshot_matches_live_database_exactly()
    {
        $snapshot = require database_path('seeds/data/menu_lists_cns.php');
        $live = DB::table('menu_lists')->where('Modules', 'CNS')->get();

        $duplicates = $live->groupBy('Menu_ID')->filter(function ($rows) {
            return $rows->count() > 1;
        });

        $this->assertCount(0, $duplicates, 'CNS menu_lists has duplicate Menu_ID rows: ' . $duplicates->keys()->implode(', '));
        $this->assertCount(count($snapshot), $live, 'CNS menu row count differs from the seeder snapshot.');

        $liveByMenuId = $live->keyBy('Menu_ID');
        $fields = ['Name', 'Path_Loc', 'Modules', 'Position', 'Parent_Menu', 'Urutan'];

        foreach ($snapshot as $menuId => $data) {
            $this->assertArrayHasKey($menuId, $liveByMenuId, "Snapshot menu {$menuId} is missing from live CNS menus.");

            $row = $liveByMenuId[$menuId];
            foreach ($fields as $index => $field) {
                $this->assertSame(
                    (string) $data[$index],
                    (string) $row->{$field},
                    "Snapshot field {$field} for {$menuId} does not match live value."
                );
            }
        }

        foreach ($live as $row) {
            $this->assertArrayHasKey(
                $row->Menu_ID,
                $snapshot,
                "Live CNS menu {$row->Menu_ID} ({$row->Name}) is not present in the seeder snapshot."
            );
        }

        $this->assertArrayNotHasKey('SI00337', $snapshot);
        $this->assertArrayNotHasKey('SI00339', $snapshot);
        $this->assertArrayNotHasKey('SI00306', $snapshot);
        $this->assertArrayNotHasKey('SI01055', $snapshot);
        $this->assertSame('Goods Receipt', $snapshot['SI01075'][0]);
        $this->assertSame('SS00086', $snapshot['SI01075'][4]);
        $this->assertSame('Master Tangki', $snapshot['SI01074'][0]);
        $this->assertSame('Master Kemasan', $snapshot['SI00342'][0]);
        $this->assertSame('Dashboard', $snapshot['SM00080'][0]);
        $this->assertSame('Dashboard', $snapshot['SS00150'][0]);
        $this->assertSame('cns-dashboard-export-volume', $snapshot['SI01150'][1]);
        $this->assertSame('1', (string) $snapshot['SM00080'][5]);
        $this->assertSame('2', (string) $snapshot['SM00065'][5]);
    }


    /** @test */
    public function cns_permissions_point_to_the_live_cns_menu_ids()
    {
        $expected = [
            'cns.master_kemasan.view' => 'SI00342',
            'cns.master_kemasan.create' => 'SI00342',
            'cns.master_tank.view' => 'SI01074',
            'cns.master_tank.create' => 'SI01074',
            'cns.goods_receipt.view' => 'SI01075',
            'cns.goods_receipt.create' => 'SI01075',
            'cns.export_volume.view' => 'SI01150',
        ];

        foreach ($expected as $permissionName => $menuId) {
            $permission = DB::table('permissions')->where('name', $permissionName)->first();
            $this->assertNotNull($permission, "Permission {$permissionName} is missing.");
            $this->assertSame($menuId, $permission->menu_id, "Permission {$permissionName} points to the wrong menu id.");
        }

        $this->assertSame(0, DB::table('permissions')
            ->where('name', 'like', 'cns.master_tank.%')
            ->where('menu_id', 'SI00339')
            ->count(), 'cns.master_tank permissions still point to the Credential menu SI00339.');
    }

    /** @test */
    public function admin_sidebar_shows_master_kemasan_and_master_tangki()
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        Auth::login($admin);

        $html = view('layout.adminor.sidebar', [
            'adminorMenuModule' => 'CNS',
            'adminorHome' => 'home-cns',
        ])->render();

        $this->assertStringContainsString('Master Kemasan', $html);
        $this->assertStringContainsString('Master Tangki', $html);
    }


    /** @test */
    public function credential_role_editor_reads_cns_permissions_for_adm()
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        Auth::login($admin);

        $role = Role::where('name', 'ADM')->whereNull('department')->firstOrFail();
        $response = app(CredentialRoleController::class)
            ->rolePermissionsData(new Request(['role_id' => $role->id]));

        $payload = json_decode($response->getContent(), true);
        $rows = collect($payload['data'] ?? [])->keyBy('menu_id');

        foreach (['SI00342', 'SI01074', 'SI01075'] as $menuId) {
            $this->assertArrayHasKey($menuId, $rows->all());
            $this->assertTrue((bool) $rows[$menuId]['view'], "ADM should have view permission for {$menuId}.");
        }
    }

    /** @test */
    public function hrd_role_is_not_granted_master_kemasan_for_now()
    {
        $hrd = \App\Role::where('name', 'HRD')->whereNull('department')->first();
        $this->assertNotNull($hrd);

        $this->assertSame(0, DB::table('role_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('role_has_permissions.role_id', $hrd->id)
            ->where('permissions.name', 'like', 'cns.master_kemasan.%')
            ->count(), 'HRD role must stay excluded from CNS Master Kemasan for now.');
    }

    /** @test */
    public function cns_permission_map_uses_the_live_menu_ids()
    {
        $this->assertSame('goods_receipt', CnsPermissionService::getSlug('SI01075'));
        $this->assertNull(CnsPermissionService::getSlug('SI01055'));
        $this->assertSame('master_tank', CnsPermissionService::getSlug('SI01074'));
        $this->assertSame('master_kemasan', CnsPermissionService::getSlug('SI00342'));
        $this->assertNull(CnsPermissionService::getSlug('SI00337'));
        $this->assertNull(CnsPermissionService::getSlug('SI00339'));
        $this->assertSame('folder_dashboard', CnsPermissionService::getSlug('SM00080'));
        $this->assertSame('folder_sub_dashboard', CnsPermissionService::getSlug('SS00150'));
        $this->assertSame('export_volume', CnsPermissionService::getSlug('SI01150'));
    }
}
