<?php

namespace Tests\Feature\CNS;

use App\BuyerContract;
use App\BuyerContractBooking;
use App\CnsCustomsClearance;
use App\Http\Controllers\CNS\CustomsClearanceController;
use App\Http\Middleware\LogUserAccess;
use App\Services\CNS\CnsPermissionService;
use App\Support\DhtmlxGridPagination;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class CustomsClearanceTest extends TestCase
{
    /** @var array<int, string> */
    private $createdFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $path) {
            $full = public_path($path);
            if (is_file($full)) {
                @unlink($full);
            }
        }
        $this->createdFiles = [];

        parent::tearDown();
    }

    public function test_menu_permission_routes_and_views_are_registered()
    {
        $menus = require base_path('database/seeds/data/menu_lists_cns.php');

        $this->assertArrayHasKey('SI00356', $menus);
        $this->assertSame(['Customs Clearance', 'cns-export-customs-clearance', 'CNS', 4, 'SS00082', 11], $menus['SI00356']);
        $this->assertSame('customs_clearance', CnsPermissionService::getSlug('SI00356'));
        $this->assertSame('SI00356', CnsPermissionService::getMenuId('customs_clearance'));
        $this->assertSame('cns.customs_clearance.view', CnsPermissionService::permissionName('SI00356', 'view'));
        $this->assertSame('cns.customs_clearance.edit', CnsPermissionService::permissionName('SI00356', 'edit'));

        $routes = collect(Route::getRoutes()->getRoutes());
        $expected = [
            'cns-export-customs-clearance'                     => 'can:cns.customs_clearance.view',
            'xml-cns-export-customs-clearance'                 => 'can:cns.customs_clearance.view',
            'cns-export-customs-clearance-detail'              => 'can:cns.customs_clearance.view',
            'cns-export-customs-clearance-file/{id}/{docType}' => 'can:cns.customs_clearance.view',
            'cns-export-customs-clearance-save'                => 'can:cns.customs_clearance.edit',
        ];

        foreach ($expected as $uri => $middleware) {
            $route = $routes->first(function ($r) use ($uri) {
                return $r->uri() === $uri;
            });
            $this->assertNotNull($route, "Route {$uri} not found");
            $this->assertContains($middleware, $route->gatherMiddleware());
        }

        $this->assertTrue(view()->exists('cns.export.customs-clearance'));
        $this->assertTrue(view()->exists('cns.export.customs-clearance-detail'));

        $indexRaw = file_get_contents(resource_path('views/cns/export/customs-clearance.blade.php'));
        $detailRaw = file_get_contents(resource_path('views/cns/export/customs-clearance-detail.blade.php'));
        $this->assertStringNotContainsString('JudulContent', $indexRaw . $detailRaw);
        $this->assertStringNotContainsString('hdr_back.gif', $indexRaw . $detailRaw);
        $this->assertStringContainsString('cns_doc', $indexRaw);
        $this->assertStringContainsString('cnsOpenDocPreview', $detailRaw);
    }

    public function test_staged_upload_preserves_existing_docs_and_file_endpoint_validates_doc_type()
    {
        $this->withoutMiddleware(LogUserAccess::class);

        Gate::before(function () {
            return true;
        });

        $user = new User();
        $user->id = 1;
        $user->username = 'cns_tester';
        $user->Name = 'CNS Tester';
        $user->Priv_ID = 'ADM';
        $user->Sub_Menu = 'SM00058,SS00082,SI00356';
        $this->be($user);

        DB::shouldReceive('table')->with('menu_lists')->andReturnSelf();
        DB::shouldReceive('where')->andReturnSelf();
        DB::shouldReceive('orderBy')->andReturnSelf();
        DB::shouldReceive('get')->andReturn(collect());

        $contract = (new BuyerContract())->forceFill([
            'ID'          => 10,
            'Contract_No' => 'CTR-CC-001',
            'Buyer_Name'  => 'Buyer Test',
        ]);
        $booking = (new BuyerContractBooking())->forceFill([
            'ID'           => 25,
            'Contract_ID'  => 10,
            'Label'        => '00001',
            'Booking_No'   => 'BC-CC-001',
            'Created_Date' => '2026-11-25 10:00:00',
        ]);
        $booking->setRelation('contract', $contract);

        $store = new \stdClass();
        $store->records = [];
        $store->nextId = 1;

        $testController = new class($booking, $store) extends CustomsClearanceController {
            private $booking;
            private $store;

            public function __construct(BuyerContractBooking $booking, \stdClass $store)
            {
                $this->booking = $booking;
                $this->store = $store;
            }

            protected function paginateBookings(Request $request): array
            {
                return DhtmlxGridPagination::paginateCollection(collect([$this->booking]), $request, [
                    'default_page_size' => 25,
                ]);
            }

            protected function fetchClearancesByBookingIds(array $bookingIds): Collection
            {
                return collect($this->store->records)->filter(function ($rec) use ($bookingIds) {
                    return in_array((int) $rec->booking_id, array_map('intval', $bookingIds), true);
                })->keyBy('booking_id');
            }

            protected function findBookingWithContractOrFail($id): BuyerContractBooking
            {
                if ((int) $id !== (int) $this->booking->ID) {
                    throw new NotFoundHttpException('Booking not found');
                }
                return $this->booking;
            }

            protected function findOrNewClearance(int $bookingId): CnsCustomsClearance
            {
                foreach ($this->store->records as $rec) {
                    if ((int) $rec->booking_id === $bookingId) {
                        return $rec;
                    }
                }
                $model = new CnsCustomsClearance();
                $model->booking_id = $bookingId;
                return $model;
            }

            protected function persistClearance(CnsCustomsClearance $clearance): void
            {
                if (!$clearance->exists) {
                    $clearance->id = $this->store->nextId++;
                    $clearance->exists = true;
                }
                $this->store->records[$clearance->id] = $clearance;
            }

            protected function findClearanceByIdOrFail($id): CnsCustomsClearance
            {
                if (!isset($this->store->records[(int) $id])) {
                    throw new NotFoundHttpException('Clearance not found');
                }
                return $this->store->records[(int) $id];
            }

            protected function bookingExistsRule(): string
            {
                return 'min:1';
            }
        };

        $this->app->instance(CustomsClearanceController::class, $testController);

        // Index page renders cleanly with page header & grid
        $indexRes = $this->get('/cns-export-customs-clearance');
        $indexRes->assertStatus(200);
        $indexRes->assertSee('Customs Clearance');
        $indexRes->assertSee('CustomsClearanceGrid');

        // Stage 1: upload doc_3d & peb
        $res1 = $this->post('/cns-export-customs-clearance-save', [
            'booking_id' => 25,
            'doc_3d'     => UploadedFile::fake()->create('dokumen-3d.pdf', 50, 'application/pdf'),
            'peb'        => UploadedFile::fake()->image('peb-scan.jpg'),
            'notes'      => 'Tahap 1 selesai',
        ]);

        $res1->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('uploaded_count', 2);

        $clearance = $store->records[1];
        $this->assertNotEmpty($clearance->doc_3d_path);
        $this->assertFileExists(public_path($clearance->doc_3d_path));
        $this->assertSame('dokumen-3d.pdf', $clearance->doc_3d_name);
        $this->assertNotEmpty($clearance->peb_path);
        $this->assertFileExists(public_path($clearance->peb_path));
        $this->assertSame('peb-scan.jpg', $clearance->peb_name);
        $this->assertNull($clearance->npe_path);
        $this->assertNull($clearance->sphib_path);
        $this->assertSame('Tahap 1 selesai', $clearance->notes);
        $this->assertSame('cns_tester', $clearance->updated_by);

        $this->createdFiles[] = $clearance->doc_3d_path;
        $this->createdFiles[] = $clearance->peb_path;

        $firstDoc3dPath = $clearance->doc_3d_path;
        $firstPebPath = $clearance->peb_path;

        // XML reflects 2/4 and cns_doc cells after Stage 1
        $xml1 = $this->get('/xml-cns-export-customs-clearance?posStart=0&count=25');
        $xml1->assertStatus(200);
        $xml1Body = $xml1->getContent();
        $this->assertStringContainsString('<cell>CTR-CC-001</cell>', $xml1Body);
        $this->assertStringContainsString('<cell>BC-CC-001</cell>', $xml1Body);
        $this->assertStringContainsString('<cell>2/4</cell>', $xml1Body);
        $this->assertStringContainsString("cns-export-customs-clearance-file/{$clearance->id}/doc_3d^dokumen-3d.pdf", $xml1Body);
        $this->assertStringContainsString("cns-export-customs-clearance-file/{$clearance->id}/peb^peb-scan.jpg", $xml1Body);
        $this->assertStringContainsString('<cell>-</cell>', $xml1Body);

        // Stage 2: upload npe & sphib without re-uploading doc_3d & peb
        $res2 = $this->post('/cns-export-customs-clearance-save', [
            'booking_id' => 25,
            'npe'        => UploadedFile::fake()->create('npe-doc.pdf', 50, 'application/pdf'),
            'sphib'      => UploadedFile::fake()->image('sphib-doc.png'),
        ]);

        $res2->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('uploaded_count', 4);

        $this->assertSame($firstDoc3dPath, $clearance->doc_3d_path);
        $this->assertFileExists(public_path($firstDoc3dPath));
        $this->assertSame('dokumen-3d.pdf', $clearance->doc_3d_name);
        $this->assertSame($firstPebPath, $clearance->peb_path);
        $this->assertFileExists(public_path($firstPebPath));
        $this->assertSame('peb-scan.jpg', $clearance->peb_name);
        $this->assertNotEmpty($clearance->npe_path);
        $this->assertFileExists(public_path($clearance->npe_path));
        $this->assertSame('npe-doc.pdf', $clearance->npe_name);
        $this->assertNotEmpty($clearance->sphib_path);
        $this->assertFileExists(public_path($clearance->sphib_path));
        $this->assertSame('sphib-doc.png', $clearance->sphib_name);
        $this->assertSame('Tahap 1 selesai', $clearance->notes);

        $this->createdFiles[] = $clearance->npe_path;
        $this->createdFiles[] = $clearance->sphib_path;

        // XML reflects 4/4 after Stage 2
        $xml2 = $this->get('/xml-cns-export-customs-clearance?posStart=0&count=25');
        $xml2->assertStatus(200);
        $this->assertStringContainsString('<cell>4/4</cell>', $xml2->getContent());

        // Detail fragment renders 4/4 state & cnsOpenDocPreview buttons
        $detail = $this->get('/cns-export-customs-clearance-detail?Task=25');
        $detail->assertStatus(200);
        $detail->assertSee('4/4 Dokumen');
        $detail->assertSee('dokumen-3d.pdf');
        $detail->assertSee('peb-scan.jpg');
        $detail->assertSee('npe-doc.pdf');
        $detail->assertSee('sphib-doc.png');
        $detail->assertSee('cnsOpenDocPreview', false);

        // File endpoint serves valid docType and rejects invalid docType with 404
        $fileRes = $this->get("/cns-export-customs-clearance-file/{$clearance->id}/doc_3d");
        $fileRes->assertStatus(200);
        $this->get("/cns-export-customs-clearance-file/{$clearance->id}/invalid_type")->assertStatus(404);

        // Stage 3: replacing doc_3d removes old physical file and preserves peb/npe/sphib
        $res3 = $this->post('/cns-export-customs-clearance-save', [
            'booking_id' => 25,
            'doc_3d'     => UploadedFile::fake()->create('dokumen-3d-rev2.pdf', 60, 'application/pdf'),
        ]);
        $res3->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('uploaded_count', 4);

        $this->assertNotSame($firstDoc3dPath, $clearance->doc_3d_path);
        $this->assertFileNotExists(public_path($firstDoc3dPath));
        $this->assertFileExists(public_path($clearance->doc_3d_path));
        $this->assertSame('dokumen-3d-rev2.pdf', $clearance->doc_3d_name);
        $this->assertSame($firstPebPath, $clearance->peb_path);
        $this->createdFiles[] = $clearance->doc_3d_path;

        // Reject invalid MIME type
        $badUpload = $this->postJson('/cns-export-customs-clearance-save', [
            'booking_id' => 25,
            'doc_3d'     => UploadedFile::fake()->create('malware.exe', 10),
        ]);
        $badUpload->assertStatus(422);

        // Path traversal attempt in stored path is rejected with 404
        $clearance->doc_3d_path = '../../.env';
        $this->get("/cns-export-customs-clearance-file/{$clearance->id}/doc_3d")->assertStatus(404);
    }
}
