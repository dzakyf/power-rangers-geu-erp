<?php

namespace Tests\Feature\CNS;

use App\AnnexVII;
use App\Http\Controllers\CNS\AnnexVIIController;
use App\Http\Controllers\CNS\SustainabilityDeclarationController;
use App\Http\Middleware\LogUserAccess;
use App\Support\DhtmlxGridPagination;
use App\SustainabilityDeclaration;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SdAndAnnexApprovalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(LogUserAccess::class);
    }

    private function adminUser(): User
    {
        $user = new User();
        $user->id = 42;
        $user->username = 'cns_approver';
        $user->Name = 'CNS Approver';
        $user->Priv_ID = 'ADM';
        $user->Location = 'SBY';
        return $user;
    }

    private function nonPrivilegedUser(): User
    {
        $user = new class extends User {
            public function hasRole($roles, string $guard = null): bool
            {
                return false;
            }

            public function can($abilities, $arguments = [])
            {
                return false;
            }
        };
        $user->id = 99;
        $user->username = 'viewer_only';
        $user->Priv_ID = 'USR';
        $user->Location = 'SBY';
        return $user;
    }

    public function test_migration_and_routes_and_initial_statuses_are_defined(): void
    {
        $migrationPath = database_path('migrations/cns/2026_11_25_000005_add_approval_columns_to_sd_and_annex_vii.php');
        $this->assertFileExists($migrationPath);
        require_once $migrationPath;
        $this->assertTrue(class_exists(\AddApprovalColumnsToSdAndAnnexVii::class));

        $migrationSrc = file_get_contents($migrationPath);
        $this->assertStringContainsString("Schema::hasTable('sustainability_declarations')", $migrationSrc);
        $this->assertStringContainsString("Schema::hasTable('annex_vii')", $migrationSrc);
        $this->assertStringContainsString("'approved_by'", $migrationSrc);
        $this->assertStringContainsString("'approved_at'", $migrationSrc);
        $this->assertStringContainsString("'Status'", $migrationSrc);

        $routes = collect(Route::getRoutes()->getRoutes());
        $expected = [
            'cns-export-sustainability-declaration-approve' => 'can:cns.sustainability_declaration.edit',
            'cns-export-annex-detail'                       => 'can:cns.annex_vii.view',
            'cns-export-annex-approve'                      => 'can:cns.annex_vii.edit',
        ];

        foreach ($expected as $uri => $middleware) {
            $route = $routes->first(function ($r) use ($uri) {
                return $r->uri() === $uri;
            });
            $this->assertNotNull($route, "Route {$uri} not found");
            $this->assertContains($middleware, $route->gatherMiddleware());
        }

        $sdControllerSrc = file_get_contents(app_path('Http/Controllers/CNS/SustainabilityDeclarationController.php'));
        $this->assertStringContainsString("\$r['Status']       = 'CREATED';", $sdControllerSrc);

        $annexControllerSrc = file_get_contents(app_path('Http/Controllers/CNS/AnnexVIIController.php'));
        $this->assertStringContainsString("\$annex->Status = 'DRAFT';", $annexControllerSrc);
        $this->assertStringContainsString("\$status = \$row->Status ?: 'DRAFT';", $annexControllerSrc);
    }

    public function test_sustainability_declaration_approve_endpoint_and_detail_view(): void
    {
        $store = new \stdClass();
        $store->records = [
            '2026000120261125' => (new SustainabilityDeclaration())->forceFill([
                'ID'                => 10,
                'SD_No'             => '2026000120261125',
                'Issue_Date'        => '2026-11-25',
                'Consignee'         => 'CONS-1',
                'Contract_No'       => 'CTR-001',
                'Quantity'          => 25.5,
                'Country_Of_Origin' => 'Indonesia',
                'Status'            => 'CREATED',
            ]),
        ];

        $controller = new class($store) extends SustainabilityDeclarationController {
            private $store;

            public function __construct(\stdClass $store)
            {
                $this->store = $store;
            }

            protected function locationSuppliers()
            {
                return collect([
                    (object) ['Supplier_ID' => 'CONS-1', 'Company_Name' => 'Buyer Example'],
                ]);
            }

            protected function findSd(Request $request): ?SustainabilityDeclaration
            {
                $task = trim((string) ($request->input('sd_no') ?: $request->input('SD_No') ?: $request->input('id') ?: $request->input('Task', '')));
                return $this->store->records[$task] ?? null;
            }

            protected function saveSd(SustainabilityDeclaration $sd): void
            {
                $this->store->records[$sd->SD_No] = $sd;
            }
        };

        $this->app->instance(SustainabilityDeclarationController::class, $controller);

        $admin = $this->adminUser();
        $guest = $this->nonPrivilegedUser();

        // Detail view shows Approve button while CREATED
        $detailBefore = $this->actingAs($admin)
            ->get('/cns-export-sustainability-declaration-detail?Task=2026000120261125')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('id="ApproveSD"', $detailBefore);
        $this->assertStringContainsString('id="UpdateSD"', $detailBefore);
        $this->assertStringContainsString('cns-export-sustainability-declaration-approve', $detailBefore);

        // Controller-level permission check rejects unauthorized user with 403
        $this->be($guest);
        $unauthResp = $controller->approve(Request::create('/cns-export-sustainability-declaration-approve', 'POST', [
            'sd_no' => '2026000120261125',
        ]));
        $this->assertSame(403, $unauthResp->getStatusCode());

        // Missing record returns 404
        $this->actingAs($admin)
            ->postJson('/cns-export-sustainability-declaration-approve', ['sd_no' => 'NON-EXISTENT'])
            ->assertStatus(404);

        // Approve succeeds
        $this->actingAs($admin)
            ->postJson('/cns-export-sustainability-declaration-approve', ['sd_no' => '2026000120261125'])
            ->assertOk()
            ->assertJson([
                'status'  => 'success',
                'message' => 'Sustainability Declaration 2026000120261125 berhasil di-approve.',
            ]);

        $sd = $store->records['2026000120261125'];
        $this->assertSame('APPROVED', $sd->Status);
        $this->assertSame(42, (int) $sd->approved_by);
        $this->assertNotNull($sd->approved_at);

        // Double approve returns 422
        $this->actingAs($admin)
            ->postJson('/cns-export-sustainability-declaration-approve', ['sd_no' => '2026000120261125'])
            ->assertStatus(422);

        // Edit blocked once APPROVED
        $this->actingAs($admin)
            ->post('/cns-export-sustainability-declaration-detail-submit', [
                'SD_No'       => '2026000120261125',
                'Contract_No' => 'CTR-HACK',
            ])
            ->assertOk()
            ->assertSee('sudah di-approve');

        // Detail view hides Approve & Update and shows APPROVED badge once APPROVED
        $detailAfter = $this->actingAs($admin)
            ->get('/cns-export-sustainability-declaration-detail?Task=2026000120261125')
            ->assertOk()
            ->getContent();
        $this->assertStringNotContainsString('id="ApproveSD"', $detailAfter);
        $this->assertStringNotContainsString('id="UpdateSD"', $detailAfter);
        $this->assertStringContainsString('APPROVED', $detailAfter);
    }

    public function test_annex_vii_approve_endpoint_xml_and_detail_view(): void
    {
        $store = new \stdClass();
        $store->records = [
            15 => (new AnnexVII())->forceFill([
                'id'           => 15,
                'Annex_No'     => '001/ANX/XI/2026',
                'SI_No'        => 'FSI-001',
                'Order_No'     => 'ORD-001',
                'Product_Name' => 'UCO',
                'Status'       => 'DRAFT',
            ]),
        ];

        $controller = new class($store) extends AnnexVIIController {
            private $store;

            public function __construct(\stdClass $store)
            {
                $this->store = $store;
            }

            protected function paginateAnnexes($query, Request $request, array $colMap): array
            {
                return DhtmlxGridPagination::paginateCollection(collect(array_values($this->store->records)), $request, [
                    'default_page_size' => 25,
                ]);
            }

            protected function findAnnex(Request $request): ?AnnexVII
            {
                $candidates = array_filter([
                    $request->input('id'),
                    $request->input('annex_no'),
                    $request->input('Annex_No'),
                    $request->input('si_no'),
                    $request->input('SI_No'),
                    $request->input('Task'),
                ], function ($v) {
                    return trim((string) $v) !== '';
                });

                foreach ($this->store->records as $rec) {
                    foreach ($candidates as $needle) {
                        $needle = trim((string) $needle);
                        if ((string) $rec->id === $needle || $rec->Annex_No === $needle || $rec->SI_No === $needle) {
                            return $rec;
                        }
                    }
                }
                return null;
            }

            protected function saveAnnex(AnnexVII $annex): void
            {
                $this->store->records[$annex->id] = $annex;
            }
        };

        $this->app->instance(AnnexVIIController::class, $controller);

        $admin = $this->adminUser();
        $guest = $this->nonPrivilegedUser();

        // XML grid shows SI_No and DRAFT (not APPROVED)
        $xmlBefore = $this->actingAs($admin)
            ->get('/xml-cns-export-annex?posStart=0&count=25')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('FSI-001', $xmlBefore);
        $this->assertStringContainsString('DRAFT', $xmlBefore);
        $this->assertStringNotContainsString('APPROVED', $xmlBefore);

        // Detail view resolves by SI_No and renders Approve button
        $detailBefore = $this->actingAs($admin)
            ->get('/cns-export-annex-detail?Task=FSI-001')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('id="ApproveAnnex"', $detailBefore);
        $this->assertStringContainsString('cns-export-annex-approve', $detailBefore);
        $this->assertStringContainsString('001/ANX/XI/2026', $detailBefore);

        // Controller-level permission check rejects unauthorized user with 403
        $this->be($guest);
        $unauthResp = $controller->approve(Request::create('/cns-export-annex-approve', 'POST', [
            'annex_no' => '001/ANX/XI/2026',
        ]));
        $this->assertSame(403, $unauthResp->getStatusCode());

        // Missing record returns 404
        $this->actingAs($admin)
            ->postJson('/cns-export-annex-approve', ['annex_no' => 'NON-EXISTENT'])
            ->assertStatus(404);

        // Approve succeeds
        $this->actingAs($admin)
            ->postJson('/cns-export-annex-approve', ['id' => 15])
            ->assertOk()
            ->assertJson([
                'status'  => 'success',
                'message' => 'Annex VII 001/ANX/XI/2026 berhasil di-approve.',
            ]);

        $annex = $store->records[15];
        $this->assertSame('APPROVED', $annex->Status);
        $this->assertSame(42, (int) $annex->approved_by);
        $this->assertNotNull($annex->approved_at);

        // Double approve returns 422
        $this->actingAs($admin)
            ->postJson('/cns-export-annex-approve', ['annex_no' => '001/ANX/XI/2026'])
            ->assertStatus(422);

        // XML grid reflects APPROVED
        $xmlAfter = $this->actingAs($admin)
            ->get('/xml-cns-export-annex?posStart=0&count=25')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('APPROVED', $xmlAfter);

        // Detail view hides Approve button and shows APPROVED badge once APPROVED
        $detailAfter = $this->actingAs($admin)
            ->get('/cns-export-annex-detail?Task=001/ANX/XI/2026')
            ->assertOk()
            ->getContent();
        $this->assertStringNotContainsString('id="ApproveAnnex"', $detailAfter);
        $this->assertStringContainsString('APPROVED', $detailAfter);
    }
}
