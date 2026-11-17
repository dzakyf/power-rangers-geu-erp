<?php

namespace Tests\Unit\CNS;

use Tests\TestCase;
use App\Services\CNS\CnsPermissionService;

class CnsPermissionFormatTest extends TestCase
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    /** @test */
    public function every_cns_menu_in_seeder_has_a_mapped_slug(): void
    {
        $menuFile = database_path('seeds/data/menu_lists_cns.php');
        $this->assertFileExists($menuFile);

        $cnsMenus = require $menuFile;
        $this->assertIsArray($cnsMenus);

        $mappedSlugs = CnsPermissionService::allMenuSlugs();

        foreach ($cnsMenus as $menuId => $menuData) {
            $name = $menuData[0];
            // Skip empty placeholder entries
            if (empty(trim($name))) {
                continue;
            }

            $this->assertArrayHasKey(
                $menuId,
                $mappedSlugs,
                "Menu_ID {$menuId} ({$name}) must be mapped in CnsPermissionService."
            );

            $slug = $mappedSlugs[$menuId];
            $this->assertNotEmpty($slug, "Slug for {$menuId} must not be empty.");
            $this->assertRegExp('/^[a-z0-9_]+$/', $slug, "Slug '{$slug}' for {$menuId} must be lowercase snake_case.");
        }
    }

    /** @test */
    public function all_mapped_slugs_are_unique(): void
    {
        $mappedSlugs = CnsPermissionService::allMenuSlugs();
        $slugValues = array_values($mappedSlugs);

        $counts = array_count_values($slugValues);
        $duplicates = array_filter($counts, function ($count) {
            return $count > 1;
        });

        $this->assertEmpty(
            $duplicates,
            'All CNS menu slugs must be unique. Duplicates found: ' . json_encode($duplicates)
        );
    }

    /** @test */
    public function permission_names_follow_cns_menu_name_action_format(): void
    {
        $mappedSlugs = CnsPermissionService::allMenuSlugs();

        foreach ($mappedSlugs as $menuId => $slug) {
            foreach (self::ACTIONS as $action) {
                $permissionName = CnsPermissionService::permissionName($menuId, $action);

                $this->assertRegExp(
                    '/^cns\.[a-z0-9_]+\.(view|create|edit|delete|approve)$/',
                    $permissionName,
                    "Permission name '{$permissionName}' does not match required format 'cns.menu_name.action'."
                );

                $this->assertEquals("cns.{$slug}.{$action}", $permissionName);
            }
        }
    }

    /** @test */
    public function helper_can_menu_and_cns_can_resolve_cns_permissions(): void
    {
        $this->assertTrue(function_exists('can_menu'));
        $this->assertTrue(function_exists('cns_can'));

        // Unauthenticated check should return false safely without exception
        $this->assertFalse(can_menu('SI00307', 'view'));
        $this->assertFalse(cns_can('shipping_instruction', 'view'));
    }

    /** @test */
    public function reverse_lookup_from_slug_to_menu_id_works(): void
    {
        $this->assertEquals('SI00307', CnsPermissionService::getMenuId('shipping_instruction'));
        $this->assertEquals('SI00308', CnsPermissionService::getMenuId('final_shipping_instruction'));
        $this->assertEquals('SI00309', CnsPermissionService::getMenuId('packing_list'));
        $this->assertEquals('SI00345', CnsPermissionService::getMenuId('offering_letter'));
        $this->assertEquals('SI00331', CnsPermissionService::getMenuId('buyer_contract'));
        $this->assertNull(CnsPermissionService::getMenuId('non_existent_slug'));
    }

    /** @test */
    public function is_cns_menu_identifies_cns_menus_correctly(): void
    {
        $this->assertTrue(CnsPermissionService::isCnsMenu('SI00307'));
        $this->assertTrue(CnsPermissionService::isCnsMenu('MS00011'));
        $this->assertFalse(CnsPermissionService::isCnsMenu('MS00001'));
        $this->assertFalse(CnsPermissionService::isCnsMenu('SI99999'));
    }
}
