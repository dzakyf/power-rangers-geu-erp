<?php

namespace Tests\Unit\CNS;

use App\Http\Controllers\CNS\TankDrainingController;
use App\TankDraining;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use Tests\TestCase;

class TankDrainingQtySludgeTest extends TestCase
{
    /** @test */
    public function model_and_migration_are_configured_for_qty_sludge()
    {
        $model = new TankDraining();
        $this->assertContains('qty_sludge', $model->getFillable());
        $this->assertSame('float', $model->getCasts()['qty_sludge'] ?? null);

        $migration = file_get_contents(
            database_path('migrations/cns/2026_11_18_000001_add_qty_sludge_to_cns_tank_drainings_table.php')
        );
        $this->assertStringContainsString('qty_sludge', $migration);
    }

    /** @test */
    public function qty_sludge_validation_allows_null_or_non_negative_and_rejects_negative()
    {
        $controller = new TankDrainingController();
        $method = new ReflectionMethod(TankDrainingController::class, 'validateDrainingRequest');
        $method->setAccessible(true);

        $rules = $method->invoke($controller, Request::create('/', 'POST'), null)->getRules();

        $this->assertFalse(Validator::make([], ['qty_sludge' => $rules['qty_sludge']])->fails());
        $this->assertFalse(Validator::make(['qty_sludge' => 0], ['qty_sludge' => $rules['qty_sludge']])->fails());
        $this->assertFalse(Validator::make(['qty_sludge' => 25.5], ['qty_sludge' => $rules['qty_sludge']])->fails());
        $this->assertTrue(Validator::make(['qty_sludge' => -0.01], ['qty_sludge' => $rules['qty_sludge']])->fails());
    }

    /** @test */
    public function views_include_qty_sludge_fields_and_column()
    {
        $newView = file_get_contents(resource_path('views/cns/inventory/tank-draining-new.blade.php'));
        $detailView = file_get_contents(resource_path('views/cns/inventory/tank-draining-detail.blade.php'));
        $indexView = file_get_contents(resource_path('views/cns/inventory/tank-draining.blade.php'));

        $this->assertStringContainsString('name="qty_sludge"', $newView);
        $this->assertStringContainsString('name="qty_sludge"', $detailView);
        $this->assertStringContainsString('Qty Sludge (KG)', $indexView);
    }
}
