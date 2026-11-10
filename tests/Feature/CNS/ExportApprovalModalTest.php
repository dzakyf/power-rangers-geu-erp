<?php

namespace Tests\Feature\CNS;

use Tests\TestCase;

class ExportApprovalModalTest extends TestCase
{
    /** @test */
    public function cns_eligible_approvers_helper_is_defined(): void
    {
        $this->assertTrue(function_exists('cns_eligible_approvers'));
        $approvers = cns_eligible_approvers();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $approvers);
    }

    /** @test */
    public function si_index_view_contains_approval_modal_and_submit_function(): void
    {
        $content = file_get_contents(resource_path('views/cns/export/si.blade.php'));

        $this->assertStringContainsString('id="approveSI"', $content);
        $this->assertStringContainsString('onclick="ApproveSI()"', $content);
        $this->assertStringContainsString('id="modalApproveSI"', $content);
        $this->assertStringContainsString('id="approveSiUser"', $content);
        $this->assertStringContainsString('SubmitApproveSI()', $content);
        $this->assertStringContainsString('approved_by: approverId', $content);
        $this->assertStringContainsString('cns-export-shipment-instruction-approve', $content);
        $this->assertStringContainsString('sudah berstatus APPROVED', $content);
        $this->assertStringContainsString('Konfirmasi Approval', $content);
    }

    /** @test */
    public function fsi_index_view_contains_approval_modal_and_submit_function(): void
    {
        $content = file_get_contents(resource_path('views/cns/export/fsi.blade.php'));

        $this->assertStringContainsString('id="approveFSI"', $content);
        $this->assertStringContainsString('onclick="ApproveFSI()"', $content);
        $this->assertStringContainsString('id="modalApproveFSI"', $content);
        $this->assertStringContainsString('id="approveFsiUser"', $content);
        $this->assertStringContainsString('SubmitApproveFSI()', $content);
        $this->assertStringContainsString('approved_by: approverId', $content);
        $this->assertStringContainsString('cns-export-final-shipment-instruction-approve', $content);
        $this->assertStringContainsString('sudah berstatus APPROVED', $content);
        $this->assertStringContainsString('Konfirmasi Approval', $content);
    }

    /** @test */
    public function si_detail_view_does_not_contain_approved_by_dropdown(): void
    {
        $content = file_get_contents(resource_path('views/cns/export/si-detail.blade.php'));

        $this->assertStringNotContainsString('name="approved_by"', $content);
        $this->assertStringNotContainsString('id="approved_by"', $content);
    }

    /** @test */
    public function fsi_detail_view_does_not_contain_approved_by_dropdown(): void
    {
        $content = file_get_contents(resource_path('views/cns/export/fsi-detail.blade.php'));

        $this->assertStringNotContainsString('name="approved_by"', $content);
        $this->assertStringNotContainsString('id="approved_by"', $content);
    }

    /** @test */
    public function si_and_fsi_print_templates_render_signature_when_approved(): void
    {
        $siPrint = file_get_contents(resource_path('views/cns/export/print/si.blade.php'));
        $this->assertStringContainsString('approver_signature', $siPrint);
        $this->assertStringContainsString('APPROVED', $siPrint);

        $fsiPrint = file_get_contents(resource_path('views/cns/export/print/fsi.blade.php'));
        $this->assertStringContainsString('approverSignature', $fsiPrint);
        $this->assertStringContainsString('APPROVED', $fsiPrint);
    }
}
