<?php

namespace Tests\Unit\CNS;

use App\Services\CNS\CnsPermissionService;
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
        $this->assertSame('1', $snapshot['SM00080'][5]);
        $this->assertSame('2', $snapshot['SM00065'][5]);
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
