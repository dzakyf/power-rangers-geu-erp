<?php

namespace Tests\Feature\BusinessPartner;

use App\Http\Controllers\RenewalController;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use ZipArchive;

/**
 * Renewal / Termination / Transfer membership:
 *  - nomor kontrak kemitraan = opportunity_partner.lead_id (in the document)
 *  - renewal: jangka waktu (plain text) reflected in the document
 *  - termination: tanggal efektif terminasi reflected in the document
 *  - transfer: tanggal efektif / status / biaya reflected in the document
 */
class RenewalTerminationTransferDocsTest extends TestCase
{
    use DatabaseTransactions;

    private function admin()
    {
        return \App\User::where('username', 'admin')->firstOrFail();
    }

    private function seedPartner(string $leadId, array $extra = []): int
    {
        return DB::table('opportunity_partner')->insertGetId(array_merge([
            'lead_id'         => $leadId,
            'full_name'       => 'Doc Test Partner',
            'company_name'    => 'Doc Test Co',
            'register_as'     => 'company',
            'contract_end_at' => now()->addMonths(2)->toDateString(),
            'status'          => 'ACTIVE',
            'created_at'      => now(),
            'updated_at'      => now(),
        ], $extra));
    }

    private function docxText($templateProcessor): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'doc') . '.docx';
        $templateProcessor->saveAs($tmp);

        $zip = new ZipArchive();
        $zip->open($tmp);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($tmp);

        return html_entity_decode(strip_tags($xml));
    }

    /** @test */
    public function renewal_document_contains_contract_no_and_jangka_waktu()
    {
        $id = $this->seedPartner('BP/TEST/REN/0001');

        $this->actingAs($this->admin());
        $res = app(RenewalController::class)->makeRenewalDocs([
            'opportunity_id' => $id,
            'renewal_length' => '3',
        ]);
        $this->assertIsArray($res);

        $text = $this->docxText($res['doc']);

        $this->assertStringContainsString('BP/TEST/REN/0001', $text, 'contract no = lead_id');
        $this->assertStringContainsString('3 Tahun', $text);
    }

    /** @test */
    public function termination_document_contains_contract_no_and_effective_date()
    {
        $id = $this->seedPartner('BP/TEST/TRM/0001');

        $this->actingAs($this->admin());
        $res = app(RenewalController::class)->makeTerminationDocs([
            'opportunity_id'          => $id,
            'type'                    => 'KESEPAKATAN_BERSAMA',
            'reason'                  => 'sepakat',
            'effective_termination_at' => '2026-10-15',
        ]);
        $this->assertIsArray($res);

        $text = $this->docxText($res['doc']);
        $expectedDate = Carbon::parse('2026-10-15')->translatedFormat('d F Y');

        $this->assertStringContainsString('BP/TEST/TRM/0001', $text);
        $this->assertStringContainsString($expectedDate, $text);
    }

    /** @test */
    public function transfer_document_contains_contract_no_effective_status_and_fee()
    {
        $id = $this->seedPartner('BP/TEST/TRF/0001');

        $this->actingAs($this->admin());
        $res = app(RenewalController::class)->makeTransferMembershipDocs([
            'opportunity_id'      => $id,
            'new_mitra_name'      => 'New Mitra',
            'reason'              => 'alih',
            'type'                => '1',
            'effective_transfer_at' => '2026-11-01',
            'status'              => 'ACTIVE',
            'fee'                 => '500000',
        ]);
        $this->assertIsArray($res);

        $text = $this->docxText($res['doc']);
        $expectedDate = Carbon::parse('2026-11-01')->translatedFormat('d F Y');

        $this->assertStringContainsString('BP/TEST/TRF/0001', $text);
        $this->assertStringContainsString($expectedDate, $text);
        $this->assertStringContainsString('ACTIVE', $text);
        $this->assertStringContainsString('500000', $text);
    }

    /** @test */
    public function active_mitra_data_includes_lead_id()
    {
        $user = DB::table('users')->insertGetId([
            'username' => 'mitra_doc_'.uniqid(),
            'Name' => 'Doc Mitra',
            'Position' => 'MITRA',
            'Status' => 'ACTIVE',
            'Location' => 'HQO',
        ]);

        $this->seedPartner('BP/TEST/ACTIVE/0001', ['user_row_id' => $user]);

        $xml = $this->actingAs($this->admin())
            ->get('/partner-data-active-mitra')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('BP/TEST/ACTIVE/0001', $xml);
    }

    /** @test */
    public function renewal_finalize_stores_renewed_length()
    {
        $id = $this->seedPartner('BP/TEST/REN/0002');

        $this->actingAs($this->admin())
            ->post('/partner-renewal-finalize-application', [
                'opportunity_id' => $id,
                'renewal_length' => '5',
            ])->assertOk();

        $row = DB::table('partner_renewal')->where('Opportunity_Id', $id)->first();
        $this->assertNotNull($row);
        $this->assertEquals(5, (int) $row->Renewed_Length);
    }

    /** @test */
    public function transfer_finalize_stores_effective_status_and_fee()
    {
        $id = $this->seedPartner('BP/TEST/TRF/0002');

        $this->actingAs($this->admin())
            ->post('/partner-transfer-membership-finalize-application', [
                'opportunity_id' => $id,
                'new_mitra_name' => 'New Mitra',
                'reason'         => 'alih',
                'type'           => '1',
                'effective_transfer_at' => '2026-12-01',
                'status'         => 'INACTIVE',
                'fee'            => '750000',
            ])->assertOk();

        $row = DB::table('partner_transfer_membership')->where('Opportunity_Id', $id)->first();
        $this->assertNotNull($row);
        $this->assertEquals('INACTIVE', $row->Result);
        $this->assertEquals(750000, (float) $row->Fee);
        $this->assertNotNull($row->Effective_Transfer_At);
    }
}
