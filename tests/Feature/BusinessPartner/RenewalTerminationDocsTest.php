<?php

namespace Tests\Feature\BusinessPartner;

use App\Http\Controllers\RenewalController;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Renewal and termination submissions must generate their letter from the
 * server-side template (like the transfer-of-ownership flow) instead of
 * requiring the partner to upload a filled document.
 */
class RenewalTerminationDocsTest extends TestCase
{
    use DatabaseTransactions;

    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private function testUser(): User
    {
        $user = User::query()->orderBy('id')->first();
        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }
        return $user;
    }

    private function seedPartner(string $leadId): int
    {
        return DB::table('opportunity_partner')->insertGetId([
            'full_name' => 'Renewal Docs Partner',
            'register_as' => 'individual',
            'status' => 'ACTIVE',
            'lead_id' => $leadId,
            'contract_end_at' => '2026-12-31 00:00:00',
            'user_row_id' => $this->testUser()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function renewal_finalize_generates_document_and_request_without_upload(): void
    {
        $opportunityId = $this->seedPartner('BP/TEST-RNW-DOCS/0001');

        $response = $this->actingAs($this->testUser())->post('/partner-renewal-finalize-application', [
            'opportunity_id' => $opportunityId,
        ]);

        $response->assertOk();

        $renewal = DB::table('partner_renewal')->where('Opportunity_Id', $opportunityId)->orderByDesc('ID')->first();
        $this->assertNotNull($renewal, 'A partner_renewal row must be created');
        $this->assertSame('PENDING', $renewal->Status);
        $this->assertNotNull($renewal->Contract_End_At, 'The contract end date must be carried over');

        $request = DB::table('approval_request')
            ->where('Request_Type', 'RENEWAL')
            ->where('Opportunity_Partner_Id', $opportunityId)
            ->orderByDesc('ID')->first();
        $this->assertNotNull($request, 'A RENEWAL approval request must be created');
        $this->assertSame((int) $renewal->ID, (int) $request->Map_To);
        $this->assertNotNull($request->Level_Id);

        $meta = DB::table('meta_files')->where('Topic', 'renewal')->where('Map_To', (string) $renewal->ID)->first();
        $this->assertNotNull($meta, 'The generated letter must be recorded against the renewal');
        $this->assertStringEndsWith('.docx', $meta->Path);
    }

    /** @test */
    public function termination_finalize_generates_document_and_request_without_upload(): void
    {
        $opportunityId = $this->seedPartner('BP/TEST-TRM-DOCS/0001');

        $response = $this->actingAs($this->testUser())->post('/partner-termination-finalize-application', [
            'opportunity_id' => $opportunityId,
        ]);

        $response->assertOk();

        $termination = DB::table('partner_termination')->where('Opportunity_Id', $opportunityId)->orderByDesc('ID')->first();
        $this->assertNotNull($termination, 'A partner_termination row must be created');

        $request = DB::table('approval_request')
            ->where('Request_Type', 'TERMINATION')
            ->where('Opportunity_Partner_Id', $opportunityId)
            ->orderByDesc('ID')->first();
        $this->assertNotNull($request);
        $this->assertSame((int) $termination->ID, (int) $request->Map_To);

        $meta = DB::table('meta_files')->where('Topic', 'termination')->where('Map_To', (string) $termination->ID)->first();
        $this->assertNotNull($meta, 'The generated letter must be recorded against the termination');
        $this->assertStringEndsWith('.docx', $meta->Path);
    }

    /** @test */
    public function renewal_preview_does_not_persist(): void
    {
        $opportunityId = $this->seedPartner('BP/TEST-RNW-PREV/0001');

        $before = DB::table('partner_renewal')->where('Opportunity_Id', $opportunityId)->count();

        $response = $this->actingAs($this->testUser())->post('/partner-renewal-preview-application', [
            'opportunity_id' => $opportunityId,
        ]);

        $response->assertOk();
        $this->assertSame($before, DB::table('partner_renewal')->where('Opportunity_Id', $opportunityId)->count());
    }

    /** @test */
    public function generated_renewal_letter_embeds_the_stored_signature(): void
    {
        $user = $this->testUser();
        $opportunityId = $this->seedPartner('BP/TEST-RNW-SIGN/0001');

        // Store a signature PNG for the partner's user account.
        $rel = '2026/09/test_signature.png';
        $abs = public_path('filevendor/'.$rel);
        if (!is_dir(dirname($abs))) {
            mkdir(dirname($abs), 0777, true);
        }
        file_put_contents($abs, base64_decode(self::PNG));
        DB::table('meta_files')->insert([
            'Filename' => 'test_signature.png',
            'Path' => $rel,
            'Extension' => 'png',
            'Description' => 'Signature',
            'Topic' => 'user-signature',
            'Map_To' => (string) $user->id,
            'Created_At' => now(),
        ]);

        $result = app(RenewalController::class)->makeRenewalDocs(['opportunity_id' => $opportunityId]);
        $this->assertIsArray($result);

        $tmp = tempnam(sys_get_temp_dir(), 'rnw').'.docx';
        $result['doc']->saveAs($tmp);

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($tmp) === true);
        $hasImage = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            if (strpos($zip->getNameIndex($i), 'word/media/') === 0) {
                $hasImage = true;
                break;
            }
        }
        $zip->close();
        @unlink($tmp);

        $this->assertTrue($hasImage, 'The signature image must be embedded in the generated letter');
    }
}
