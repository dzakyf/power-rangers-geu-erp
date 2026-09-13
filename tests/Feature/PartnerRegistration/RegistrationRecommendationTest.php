<?php

namespace Tests\Feature\PartnerRegistration;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The onboarding approval list "Direkomendasikan?" column reads
 * opportunity_partner.is_recommended_to_be_approved, which is derived from
 * the applicant's source-of-funds selection. The forms submit a single radio
 * group (Source_Fund / Company_Fund) but the controller only read the legacy
 * per-option names, so the value was always the default '3' (In Consideration).
 *
 * Mapping: savings/business -> 1 (Yes), loan/pension -> 2 (No),
 * otherwise -> 3 (In Consideration).
 */
class RegistrationRecommendationTest extends TestCase
{
    use DatabaseTransactions;

    private function baseFields(): array
    {
        return [
            'Register_As'            => 'individual',
            'Full_Name'              => 'Test Recommend',
            'Email'                  => 'recommend-'.uniqid().'@test.dev',
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
            'Doc_KTP'                => 'KTP',
            'Doc_KTP_File'           => 'uploads/test-ktp.jpg',
            'Doc_NPWP'               => 'NPWP',
            'Doc_NPWP_File'          => 'uploads/test-npwp.jpg',
            'NIK'                    => '3174012345670001',
            'NPWP'                   => '012345678901234',
        ];
    }

    private function submitIndividual(string $fund): void
    {
        $response = $this->post('/partner-registration-submit', array_merge($this->baseFields(), [
            'Source_Fund' => $fund,
        ]));

        $response->assertOk();
        $this->assertStringContainsString('success', $response->getContent());
    }

    private function savedRecommendation(string $name): int
    {
        return (int) DB::table('opportunity_partner')->where('full_name', $name)->value('is_recommended_to_be_approved');
    }

    /** @test */
    public function savings_fund_is_recommended(): void
    {
        $this->submitIndividual('savings');
        $this->assertSame(1, $this->savedRecommendation('Test Recommend'));
    }

    /** @test */
    public function business_fund_is_recommended(): void
    {
        $this->submitIndividual('business');
        $this->assertSame(1, $this->savedRecommendation('Test Recommend'));
    }

    /** @test */
    public function loan_fund_is_not_recommended(): void
    {
        $this->submitIndividual('loan');
        $this->assertSame(2, $this->savedRecommendation('Test Recommend'));
    }

    /** @test */
    public function pension_fund_is_not_recommended(): void
    {
        $this->submitIndividual('pension');
        $this->assertSame(2, $this->savedRecommendation('Test Recommend'));
    }

    /** @test */
    public function inheritance_fund_stays_in_consideration(): void
    {
        $this->submitIndividual('inheritance');
        $this->assertSame(3, $this->savedRecommendation('Test Recommend'));
    }

    /** @test */
    public function company_business_fund_is_recommended(): void
    {
        $fields = [
            'Register_As'             => 'company',
            'Full_Name'               => 'Test Recommend Co',
            'Company_Name'            => 'PT Test Recommend',
            'Email'                   => 'recommend-co-'.uniqid().'@test.dev',
            'Contact'                 => '081234567890',
            'C_Address_Detail'        => 'Jl. Test No. 1',
            'C_Address_RT'            => '001',
            'C_Address_RW'            => '002',
            'C_Address_Ward'          => 'Kelurahan Test',
            'C_Address_Subdistrict'   => 'Kecamatan Test',
            'C_Address_City'          => 'Kota Test',
            'C_Address_Province'      => 'DKI Jakarta',
            'C_Address_Postal'        => '10110',
            'C_Doc_Akta'              => 'Akta',
            'C_Doc_Akta_File'         => 'uploads/test-akta.pdf',
            'C_Doc_NIB'               => 'NIB',
            'C_Doc_NIB_File'          => 'uploads/test-nib.pdf',
            'C_Doc_NPWP'              => 'NPWP',
            'C_Doc_NPWP_File'         => 'uploads/test-npwp.pdf',
            'Company_Fund'            => 'business',
            'Company_Package'         => 'mobil',
        ];

        $this->post('/partner-registration-submit', $fields)
            ->assertOk()
            ->assertSee('success');

        $this->assertSame(1, $this->savedRecommendation('Test Recommend Co'));
    }
}
