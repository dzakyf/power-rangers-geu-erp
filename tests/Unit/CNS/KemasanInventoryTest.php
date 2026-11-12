<?php

namespace Tests\Unit\CNS;

use App\CnsInventoryKemasan;
use App\Http\Controllers\CNS\KemasanInventoryController;
use App\Services\CNS\KemasanInventoryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KemasanInventoryTest extends TestCase
{

    /** @test */
    public function service_classes_are_instantiable()
    {
        $service = new KemasanInventoryService();
        $this->assertInstanceOf(KemasanInventoryService::class, $service);

        $controller = new KemasanInventoryController($service);
        $this->assertInstanceOf(KemasanInventoryController::class, $controller);
    }

    /** @test */
    public function menu_lists_cns_has_new_structure()
    {
        $menuData = file_get_contents(database_path('seeds/data/menu_lists_cns.php'));

        // Sub menus (Position 3)
        $this->assertStringContainsString("'SS00087' => ['Inventory UCO', '#', 'CNS', 3, 'SM00063', 1]", $menuData);
        $this->assertStringContainsString("'SS00091' => ['Inventory Non UCO', '#', 'CNS', 3, 'SM00063', 2]", $menuData);

        // Sub menu items (Position 4)
        $this->assertStringContainsString("'SI00317' => ['Inbound Movement', 'cns-inventory-tracking-index', 'CNS', 4, 'SS00087', 1]", $menuData);
        $this->assertStringContainsString("'SI00340' => ['Tank Draining', 'cns-tank-draining', 'CNS', 4, 'SS00087', 2]", $menuData);
        $this->assertStringContainsString("'SI00341' => ['Inventory Kemasan', 'cns-inventory-kemasan', 'CNS', 4, 'SS00091', 1]", $menuData);
        $this->assertStringContainsString("'SI00336' => ['Inventory Non-UCO', 'cns-inventory-non-uco', 'CNS', 4, 'SS00091', 2]", $menuData);
    }

    /** @test */
    public function migration_file_contains_expected_definitions()
    {
        $migrationFile = database_path('migrations/cns/2026_11_10_000003_create_cns_inventory_kemasan_and_update_inventory_menus.php');
        $this->assertFileExists($migrationFile);

        $content = file_get_contents($migrationFile);
        $this->assertStringContainsString('cns_inventory_kemasan', $content);
        $this->assertStringContainsString('SS00091', $content);
        $this->assertStringContainsString('Inventory Non UCO', $content);
        $this->assertStringContainsString('Inventory UCO', $content);
        $this->assertStringContainsString('Inbound Movement', $content);
        $this->assertStringContainsString('SI00341', $content);
        $this->assertStringContainsString('Inventory Kemasan', $content);
    }
}
