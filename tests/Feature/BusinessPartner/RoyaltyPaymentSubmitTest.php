<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regression tests for the History Royalty transfer-document submit
 * (partner-profit-sharing-royalty-update -> updateRoyaltyPaymentData).
 */
class RoyaltyPaymentSubmitTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('Position', 'Administrator')->firstOrFail();
    }

    private function seedPayable(array $overrides = []): int
    {
        return DB::table('account_payable')->insertGetId(array_merge([
            'Task_ID' => 'TEST-AP-' . uniqid(),
            'Reference' => 'TEST/REF/' . uniqid(),
            'Status' => 'WAITING_PAYMENT',
            'PPH_Type' => '0',
            'Total' => 100,
            'Dateinsert' => now(),
        ], $overrides));
    }

    private function linkPayable(int $payableId, int $requesterId): int
    {
        return DB::table('approval_request')->insertGetId([
            'Status' => 'NEED_APPROVAL',
            'Requester_Id' => $requesterId,
            'Description' => 'Pengajuan royalti test',
            'Request_Type' => 'PROFIT_SHARING',
            'Map_To' => $payableId,
            'Current_Level' => 0,
            'Submitted_At' => now(),
            'Created_At' => now(),
        ]);
    }

    /** @test */
    public function submitting_a_transfer_document_updates_the_payable_and_links_the_meta_file_to_the_payable(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $payableId = $this->seedPayable();
        $this->linkPayable($payableId, $admin->id);

        $response = $this->actingAs($admin)->post('/partner-profit-sharing-royalty-update', [
            'Application_Id' => $payableId,
            'Status' => 'PAID',
            'Transfer_Doc' => UploadedFile::fake()->create('proof.pdf', 10, 'application/pdf'),
        ]);

        $response->assertOk();
        $this->assertSame('success', $response->json('message'));

        $payable = DB::table('account_payable')->where('ID', $payableId)->first();
        $this->assertSame('PAID', $payable->Status);
        $this->assertNotEmpty($payable->Url_Transfer, 'The transfer proof URL must be stored on the payable');

        $meta = DB::table('meta_files')
            ->where('Topic', 'profit-sharing')
            ->where('Description', 'Bukti Transfer')
            ->orderByDesc('ID')
            ->first();

        $this->assertNotNull($meta);
        $this->assertSame((string) $payableId, (string) $meta->Map_To, 'Proof must be linked to the AP ID, not the update row count');
    }

    /** @test */
    public function submitting_without_an_application_id_is_rejected(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())->post('/partner-profit-sharing-royalty-update', [
            'Status' => 'PAID',
            'Transfer_Doc' => UploadedFile::fake()->create('proof.pdf', 10, 'application/pdf'),
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function submitting_without_a_transfer_document_is_rejected(): void
    {
        $admin = $this->admin();
        $payableId = $this->seedPayable();
        $this->linkPayable($payableId, $admin->id);

        $response = $this->actingAs($admin)->post('/partner-profit-sharing-royalty-update', [
            'Application_Id' => $payableId,
            'Status' => 'PAID',
        ]);

        $response->assertStatus(422);
    }
}
