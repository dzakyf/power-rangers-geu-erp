<?php

namespace Tests\Unit\CNS;

use App\Http\Controllers\CNS\TankDrainingController;
use App\TankDraining;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use Tests\TestCase;

class TankDrainingBastTest extends TestCase
{
    /** @test */
    public function model_and_route_and_migration_are_configured_for_bast()
    {
        $model = new TankDraining();
        $this->assertContains('bast_document_path', $model->getFillable());
        $this->assertContains('bast_document_name', $model->getFillable());

        $this->assertTrue(app('router')->has('cns-tank-draining.bast'));

        $migration = file_get_contents(
            database_path('migrations/cns/2026_11_11_000004_add_bast_document_to_cns_tank_drainings_table.php')
        );
        $this->assertStringContainsString('bast_document_path', $migration);
        $this->assertStringContainsString('bast_document_name', $migration);
    }

    /** @test */
    public function bast_validation_rules_require_file_on_create_and_missing_existing_and_optional_when_present()
    {
        $controller = new TankDrainingController();
        $method = new ReflectionMethod(TankDrainingController::class, 'validateDrainingRequest');
        $method->setAccessible(true);

        // Create: required
        $createRules = $method->invoke($controller, Request::create('/', 'POST'), null)->getRules();
        $this->assertTrue(Validator::make([], ['bast_document' => $createRules['bast_document']])->fails());
        $this->assertTrue(
            Validator::make(
                ['bast_document' => UploadedFile::fake()->create('bast.txt', 10, 'text/plain')],
                ['bast_document' => $createRules['bast_document']]
            )->fails()
        );
        $this->assertFalse(
            Validator::make(
                ['bast_document' => UploadedFile::fake()->create('bast.pdf', 100, 'application/pdf')],
                ['bast_document' => $createRules['bast_document']]
            )->fails()
        );

        // Edit when existing has no BAST: required
        $legacyRecord = new TankDraining(['draining_code' => 'DRN-OLD-0001', 'bast_document_path' => null]);
        $legacyRecord->id = 10;
        $legacyRules = $method->invoke($controller, Request::create('/', 'POST'), $legacyRecord)->getRules();
        $this->assertTrue(Validator::make([], ['bast_document' => $legacyRules['bast_document']])->fails());

        // Edit when existing already has BAST: optional
        $existingRecord = new TankDraining([
            'draining_code' => 'DRN-NEW-0001',
            'bast_document_path' => 'cns/tank-draining-bast/existing.pdf',
        ]);
        $existingRecord->id = 11;
        $editRules = $method->invoke($controller, Request::create('/', 'POST'), $existingRecord)->getRules();
        $this->assertFalse(Validator::make([], ['bast_document' => $editRules['bast_document']])->fails());
    }

    /** @test */
    public function store_bast_document_saves_file_on_local_disk()
    {
        Storage::fake('local');

        $controller = new TankDrainingController();
        $method = new ReflectionMethod(TankDrainingController::class, 'storeBastDocument');
        $method->setAccessible(true);

        $file = UploadedFile::fake()->create('bast_signed.pdf', 120, 'application/pdf');
        $meta = $method->invoke($controller, $file, 'DRN-20261111-0001');

        $this->assertSame('bast_signed.pdf', $meta['name']);
        $this->assertStringStartsWith('cns/tank-draining-bast/BAST_DRN-20261111-0001_', $meta['path']);
        Storage::disk('local')->assertExists($meta['path']);
    }
}
