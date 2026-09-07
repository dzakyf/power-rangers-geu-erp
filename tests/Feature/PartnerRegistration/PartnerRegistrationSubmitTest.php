<?php

namespace Tests\Feature\PartnerRegistration;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Integration tests: unified partner-registration submit pipeline.
 *
 * Both entry points share one controller (PartnerRegistrationController::submit):
 *   - internal (sales/admin, authenticated): POST /sales-opportunity-registration-submit
 *   - public (lead with one-time token):       POST /partner-registration-submit
 *
 * Contract: plain-text "success" / "failed ..." / "error: ...".
 * Requester_Id = authenticated submitter id (internal) or null->0 (public).
 * opportunity_partner.user_row_id stays null (backfilled on onboarding approval).
 */
class PartnerRegistrationSubmitTest extends TestCase
{
    use DatabaseTransactions;

    private function seedOpportunity(?string $token): int
    {
        return DB::table('opportunity')->insertGetId([
            'Task_ID'               => 'T-REG-'.uniqid(),
            'Location'              => 'TEST',
            'Date'                  => now()->toDateString(),
            'Full_Name'             => 'Lead '.uniqid(),
            'Phone_Number'          => '081234567890',
            'Partnership_Interest'  => 1,
            'Capital_Estimation'    => 100000000,
            'Created_By'            => 'test',
            'Status'                => 'OPEN',
            'Updated_By'            => 'test',
            'registration_token'    => $token,
            'token_expires_at'      => $token ? now()->addDay() : null,
        ]);
    }

    private function baseFormFields(): array
    {
        return [
            'Register_As'            => 'individual',
            'Full_Name'              => 'Test Partner',
            'Email'                  => 'partner-'.uniqid().'@test.dev',
            'Contact'                => '081234567890',
            'I_Address_Detail'       => 'Jl. Test No. 1',
            'I_Address_RT'           => '001',
            'I_Address_RW'           => '002',
            'I_Address_Ward'         => 'Kelurahan Test',
            'I_Address_Subdistrict'  => 'Kecamatan Test',
            'I_Address_City'         => 'Kota Test',
            'I_Address_Province'     => 'DKI Jakarta',
            'I_Address_Postal'       => '10110',
            'Gender'                 => 'male',
            'Package'                => 'mobil',
            // Mandatory documents since the QA hardening (individual: KTP+NPWP)
            'Doc_KTP'                => 'KTP',
            'Doc_KTP_File'           => 'uploads/test-ktp.jpg',
            'Doc_NPWP'               => 'NPWP',
            'Doc_NPWP_File'          => 'uploads/test-npwp.jpg',
            'NIK'                    => '3174012345670001',
            'NPWP'                   => '012345678901234',
        ];
    }

    // ------------------------------------------------------------------
    // Internal flow (authenticated)
    // ------------------------------------------------------------------

    /** @test */
    public function internal_submit_as_admin_sets_requester_and_keeps_user_row_null()
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $before = DB::table('opportunity_partner')->count();

        $this->actingAs($admin)
            ->post('/sales-opportunity-registration-submit', $this->baseFormFields())
            ->assertOk()
            ->assertSee('success');

        $opp = DB::table('opportunity_partner')
            ->where('full_name', 'Test Partner')
            ->latest('id')
            ->first();

        $this->assertNotNull($opp);
        $this->assertNull($opp->user_row_id);
        $this->assertEquals('mobil', $opp->package_option);

        $approval = DB::table('approval_request')
            ->where('Opportunity_Partner_Id', $opp->id)
            ->first();

        $this->assertNotNull($approval, 'approval_request should be created');
        $this->assertEquals('ONBOARDING', $approval->Request_Type);
        $this->assertEquals($admin->id, (int) $approval->Requester_Id);

        $this->assertEquals($before + 1, DB::table('opportunity_partner')->count());
    }

    // ------------------------------------------------------------------
    // Public flow (guest with one-time token)
    // ------------------------------------------------------------------

    /** @test */
    public function public_submit_with_valid_token_succeeds_and_clears_token()
    {
        $token = 'tok-'.uniqid();
        $oppId = $this->seedOpportunity($token);

        $this->post('/partner-registration-submit', array_merge($this->baseFormFields(), [
            'registration_token' => $token,
        ]))->assertOk()->assertSee('success');

        $lead = DB::table('opportunity')->where('ID', $oppId)->first();
        $this->assertNull($lead->registration_token, 'one-time token must be cleared');

        $partner = DB::table('opportunity_partner')->where('full_name', 'Test Partner')->latest('id')->first();
        $this->assertNotNull($partner);

        $approval = DB::table('approval_request')->where('Opportunity_Partner_Id', $partner->id)->first();
        $this->assertNotNull($approval);
        $this->assertEquals('ONBOARDING', $approval->Request_Type);
    }

    /** @test */
    public function public_submit_with_invalid_token_is_rejected()
    {
        $before = DB::table('opportunity_partner')->count();

        $this->post('/partner-registration-submit', array_merge($this->baseFormFields(), [
            'registration_token' => 'bogus-token',
        ]))->assertOk()->assertSee('error: Link tidak valid atau sudah kadaluarsa');

        $this->assertEquals($before, DB::table('opportunity_partner')->count());
    }
}
