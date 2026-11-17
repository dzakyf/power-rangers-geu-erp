<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransferMembershipDownloadTest extends TestCase
{
    use DatabaseTransactions;

    /** @var string[] */
    private $createdFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        parent::tearDown();
    }

    private function admin(): User
    {
        return User::where('Position', 'Administrator')->firstOrFail();
    }

    private function seedPartner(string $leadId): int
    {
        return DB::table('opportunity_partner')->insertGetId([
            'lead_id' => $leadId,
            'full_name' => 'Download Test Partner',
            'company_name' => 'Download Test Co',
            'register_as' => 'company',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedTransfer(int $partnerId, array $overrides = []): int
    {
        return DB::table('partner_transfer_membership')->insertGetId(array_merge([
            'Opportunity_Id' => (string) $partnerId,
            'Contract_No' => 'PTF/TEST/0001',
            'New_Mitra_Name' => 'New Mitra',
            'Type' => '1',
            'Reason' => 'alih',
            'Applied_At' => now(),
            'Effective_Transfer_At' => '2026-12-01',
            'Result' => 'ACTIVE',
            'Fee' => 750000,
        ], $overrides));
    }

    private function ensureDir(string $file): void
    {
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
    }

    /** @test */
    public function transfer_membership_list_has_a_download_result_action(): void
    {
        $response = $this->actingAs($this->admin())->get('/partner-transfer-membership-index');

        $response->assertOk();
        $response->assertSee('Download Result', false);
        $response->assertSee('partner-transfer-membership-download', false);
    }

    /** @test */
    public function download_serves_the_existing_generated_document(): void
    {
        $partnerId = $this->seedPartner('BP/TEST/TRF/DL/0001');
        $transferId = $this->seedTransfer($partnerId);

        $relative = 'filevendor/2026/11/ptf_download_test_' . uniqid() . '.docx';
        $full = public_path($relative);
        $this->ensureDir($full);
        file_put_contents($full, 'DUMMY DOCX');
        $this->createdFiles[] = $full;

        $metaId = DB::table('meta_files')->insertGetId([
            'Filename' => $relative,
            'Path' => basename($relative),
            'Extension' => 'docx',
            'Description' => 'Upload dokumen transfer kemitraan',
            'Topic' => 'transfer-membership',
            'Map_To' => $transferId,
        ]);
        DB::table('partner_transfer_membership')->where('ID', $transferId)->update(['File_Id' => $metaId]);

        $response = $this->actingAs($this->admin())
            ->get('/partner-transfer-membership-download?ID=' . $transferId);

        $response->assertOk();
        $this->assertStringContainsString(
            basename($relative),
            (string) $response->headers->get('content-disposition')
        );
    }

    /** @test */
    public function download_regenerates_a_missing_document_for_an_existing_transfer(): void
    {
        $partnerId = $this->seedPartner('BP/TEST/TRF/DL/0002');
        $transferId = $this->seedTransfer($partnerId);

        $missingPath = 'filevendor/2026/11/ptf_missing_' . uniqid() . '.docx';
        $metaId = DB::table('meta_files')->insertGetId([
            'Filename' => $missingPath,
            'Path' => basename($missingPath),
            'Extension' => 'docx',
            'Description' => 'Upload dokumen transfer kemitraan',
            'Topic' => 'transfer-membership',
            'Map_To' => $transferId,
        ]);
        DB::table('partner_transfer_membership')->where('ID', $transferId)->update(['File_Id' => $metaId]);

        $response = $this->actingAs($this->admin())
            ->get('/partner-transfer-membership-download?ID=' . $transferId);

        $response->assertOk();

        $meta = DB::table('meta_files')->where('ID', $metaId)->first();
        $this->assertNotSame($missingPath, $meta->Filename, 'The meta file must point to the regenerated document');
        $this->assertFileExists(public_path($meta->Filename));
        $this->createdFiles[] = public_path($meta->Filename);
    }

    /** @test */
    public function download_returns_404_for_an_unknown_transfer(): void
    {
        $response = $this->actingAs($this->admin())
            ->get('/partner-transfer-membership-download?ID=999999999');

        $response->assertStatus(404);
    }
}
