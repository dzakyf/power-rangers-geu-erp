<?php

namespace Tests\Feature\CNS;

use App\PackingList;
use Tests\TestCase;

class PackingListApprovalTest extends TestCase
{
    /** @test */
    public function routes_for_pl_approval_and_detail_submit_are_registered(): void
    {
        $routes = app('router')->getRoutes();

        $approveRoute = $routes->getByName('cns-export-packing-list-approve')
            ?: $routes->match(app('request')->create('cns-export-packing-list-approve', 'POST'));
        $this->assertNotNull($approveRoute);

        $detailSubmitRoute = $routes->getByName('cns-export-packing-list-detail-submit')
            ?: $routes->match(app('request')->create('cns-export-packing-list-detail-submit', 'POST'));
        $this->assertNotNull($detailSubmitRoute);
    }

    /** @test */
    public function pl_index_view_contains_approve_button_and_approval_modal(): void
    {
        $content = file_get_contents(resource_path('views/cns/export/pl.blade.php'));

        $this->assertStringContainsString('id="approvePL"', $content);
        $this->assertStringContainsString('value="Approve PL"', $content);
        $this->assertStringContainsString('onclick="ApprovePL()"', $content);
        $this->assertStringContainsString('id="modalApprovePL"', $content);
        $this->assertStringContainsString('id="approvePlUser"', $content);
        $this->assertStringContainsString('SubmitApprovePL()', $content);
        $this->assertStringContainsString('approved_by: approverId', $content);
        $this->assertStringContainsString('cns-export-packing-list-approve', $content);
        $this->assertStringContainsString('sudah berstatus APPROVED', $content);
        $this->assertStringContainsString('Konfirmasi Approval', $content);
        $this->assertStringContainsString('Status', $content);
    }

    /** @test */
    public function pl_detail_view_does_not_contain_approved_by_dropdown(): void
    {
        $content = file_get_contents(resource_path('views/cns/export/pl-detail.blade.php'));

        $this->assertStringNotContainsString('name="approved_by"', $content);
        $this->assertStringNotContainsString('id="approved_by"', $content);
        $this->assertStringContainsString('cns-export-packing-list-detail-submit', $content);
    }

    /** @test */
    public function pl_new_view_does_not_contain_approved_by_dropdown(): void
    {
        $content = file_get_contents(resource_path('views/cns/export/pl-new.blade.php'));

        $this->assertStringNotContainsString('name="approved_by"', $content);
        $this->assertStringNotContainsString('id="approved_by"', $content);
    }

    /** @test */
    public function pl_print_view_renders_signature_when_approved(): void
    {
        $content = file_get_contents(resource_path('views/cns/export/print/pl.blade.php'));

        $this->assertStringContainsString('$approverSignature', $content);
        $this->assertStringContainsString('$shipment->Status === \'APPROVED\'', $content);
        $this->assertStringContainsString('$approver->Name', $content);
    }

    /** @test */
    public function packing_list_model_has_approver_relation(): void
    {
        $pl = new PackingList();
        $this->assertTrue(method_exists($pl, 'approver'));
    }
}
