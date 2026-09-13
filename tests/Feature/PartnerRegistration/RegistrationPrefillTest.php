<?php

namespace Tests\Feature\PartnerRegistration;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression tests for the assign-sales -> partner-registration flow.
 *
 * The blast link (public/partner-registration?token=...) must prefill the
 * form from the opportunity data captured earlier, and the mandatory document
 * uploads must actually be included in the POST (the hidden *_File inputs had
 * no name attribute, so FormData silently dropped them and the server
 * rejected with "File KTP wajib diunggah").
 */
class RegistrationPrefillTest extends TestCase
{
    use DatabaseTransactions;

    private const LEAD_NAME = 'Prefill Partner';
    private const LEAD_PHONE = '081234567890';
    private const LEAD_KTP = '3174012345670001';
    private const LEAD_NPWP = '012345678901234';

    private function seedOpportunity(?string $token): int
    {
        return DB::table('opportunity')->insertGetId([
            'Task_ID'              => 'T-PREFILL-'.uniqid(),
            'Location'             => 'TEST',
            'Date'                 => now()->toDateString(),
            'Full_Name'            => self::LEAD_NAME,
            'Phone_Number'         => self::LEAD_PHONE,
            'Source_Channel'       => 'Instagram',
            'Hub_Type'             => 'booth',
            'KTP'                  => self::LEAD_KTP,
            'NPWP'                 => self::LEAD_NPWP,
            'Partnership_Interest' => 2,
            'Capital_Estimation'   => 2,
            'Created_By'           => 'test',
            'Status'               => 'OPEN',
            'Updated_By'           => 'test',
            'registration_token'   => $token,
            'token_expires_at'     => $token ? now()->addDay() : null,
        ]);
    }

    /** @test */
    public function public_form_is_prefilled_from_the_opportunity(): void
    {
        $token = 'tok-'.uniqid();
        $this->seedOpportunity($token);

        $response = $this->get('/partner-registration?token='.$token);

        $response->assertOk();
        $response->assertSee('value="'.self::LEAD_NAME.'"', false);
        $response->assertSee('value="'.self::LEAD_PHONE.'"', false);
        $response->assertSee('value="'.self::LEAD_KTP.'"', false);
        $response->assertSee('value="'.self::LEAD_NPWP.'"', false);
        // Source channel + package selections reflect the lead data.
        $response->assertSee('value="Instagram" checked', false);
        $response->assertSee('value="booth" checked', false);
    }

    /** @test */
    public function public_form_keeps_document_paths_in_the_post(): void
    {
        $token = 'tok-'.uniqid();
        $this->seedOpportunity($token);

        $response = $this->get('/partner-registration?token='.$token);

        // FormData only serialises controls that have a name attribute.
        $response->assertSee('name="Doc_KTP_File"', false);
        $response->assertSee('name="Doc_NPWP_File"', false);
        $response->assertSee('name="C_Doc_Akta_File"', false);
        $response->assertSee('name="C_Doc_NIB_File"', false);
        $response->assertSee('name="C_Doc_NPWP_File"', false);
    }

    /** @test */
    public function invalid_token_shows_the_expired_notice(): void
    {
        $response = $this->get('/partner-registration?token=does-not-exist');

        $response->assertOk();
        $response->assertViewIs('partner.registration-expired');
    }

    /** @test */
    public function internal_form_serialises_the_actual_form_container(): void
    {
        $taskId = 'T-INTERNAL-'.uniqid();
        DB::table('opportunity')->insert([
            'Task_ID'              => $taskId,
            'Location'             => 'TEST',
            'Date'                 => now()->toDateString(),
            'Full_Name'            => 'Internal Prefill',
            'Phone_Number'         => '081200000000',
            'Partnership_Interest' => 2,
            'Capital_Estimation'   => 2,
            'Created_By'           => 'test',
            'Status'               => 'OPEN',
            'Updated_By'           => 'test',
        ]);

        $user = User::query()->orderBy('id')->first();
        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }

        $response = $this->actingAs($user)->get('/partner-business-registration-detail?ID='.$taskId);

        $response->assertOk();
        // getAllValues() must target the rendered form, not a wrapper that
        // does not exist (previously #ContentPages -> posted nothing).
        $response->assertSee('#registrationForm input', false);
    }
}
