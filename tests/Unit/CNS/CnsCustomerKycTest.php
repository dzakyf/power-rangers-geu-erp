<?php

namespace Tests\Unit\CNS;

use App\CnsCustomerKyc;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CnsCustomerKycTest extends TestCase
{
    public function test_roman_month_and_kyc_no_formatting()
    {
        $dateNov = Carbon::create(2026, 11, 13);
        $this->assertSame('XI', CnsCustomerKyc::romanMonth($dateNov->month));
        $this->assertSame('001/KYC-GEU/XI/2026', CnsCustomerKyc::formatKycNo(1, $dateNov));

        $dateJan = Carbon::create(2027, 1, 5);
        $this->assertSame('I', CnsCustomerKyc::romanMonth($dateJan->month));
        $this->assertSame('015/KYC-GEU/I/2027', CnsCustomerKyc::formatKycNo(15, $dateJan));
    }

    public function test_next_sequence_from_existing_numbers()
    {
        $this->assertSame(1, CnsCustomerKyc::nextSequenceFromNumbers([]));
        $this->assertSame(11, CnsCustomerKyc::nextSequenceFromNumbers([
            '001/KYC-GEU/I/2026',
            '010/KYC-GEU/XI/2026',
            '005/KYC-GEU/V/2026',
        ]));
    }

    public function test_routes_are_registered()
    {
        $this->assertTrue(Route::has('cns-customer-compliance-kyc.index'));
        $this->assertTrue(Route::has('cns-customer-compliance-kyc.xml'));
        $this->assertTrue(Route::has('cns-customer-compliance-kyc.create'));
        $this->assertTrue(Route::has('cns-customer-compliance-kyc.store'));
        $this->assertTrue(Route::has('cns-customer-compliance-kyc.detail'));
        $this->assertTrue(Route::has('cns-customer-compliance-kyc.edit'));
        $this->assertTrue(Route::has('cns-customer-compliance-kyc.update'));
        $this->assertTrue(Route::has('cns-customer-compliance-kyc.delete'));
        $this->assertTrue(Route::has('cns-customer-compliance-kyc.download'));
        $this->assertTrue(Route::has('cns-customer-compliance-kyc.print'));
    }

    public function test_supplier_accessor_handles_empty_id()
    {
        $kyc = new CnsCustomerKyc();
        $this->assertNull($kyc->supplier);
    }

    public function test_print_view_renders_correctly()
    {
        $kyc = new CnsCustomerKyc([
            'kyc_no'                   => '001/KYC-GEU/XI/2026',
            'filler_name'              => 'John Doe',
            'form_date'                => Carbon::create(2026, 11, 13),
            'company_name'             => 'Global Biofuel BV',
            'company_reg_number'       => 'NL12345678',
            'incorporation_date'       => Carbon::create(2018, 5, 20),
            'registered_address'       => 'Keizersgracht 123, Amsterdam, Netherlands',
            'admin_accounting_address' => 'Keizersgracht 123, Amsterdam, Netherlands',
            'legal_entity_type'        => 'BV',
            'vat_number'               => 'NL888999111B01',
            'website'                  => 'https://globalbiofuel.example',
            'trading_contact_person'   => 'Alice Smith',
            'trading_contact_details'  => 'Email: alice@biofuel.nl / Phone: +31 20 1234567',
            'finance_contact_person'   => 'Bob Jones',
            'finance_contact_details'  => 'Email: finance@biofuel.nl / Phone: +31 20 7654321',
            'business_activities'      => 'Trading used cooking oil and renewable feedstocks',
            'countries_of_activities'  => 'Netherlands, Germany, Belgium, Indonesia',
            'doc_register_extract'     => 'cns/kyc-docs/sample-extract.pdf',
            'doc_register_extract_name'=> 'KvK_Extract.pdf',
            'doc_certificate'          => 'cns/kyc-docs/sample-iscc.pdf',
            'doc_certificate_name'     => 'ISCC_EU_Certificate.pdf',
        ]);

        $html = view('cns.customer-compliance.kyc.print', [
            'kyc'       => $kyc,
            'headerUri' => null,
            'footerUri' => null,
        ])->render();

        $this->assertStringContainsString('KNOW-YOUR-CUSTOMER FORM', $html);
        $this->assertStringContainsString('001/KYC-GEU/XI/2026', $html);
        $this->assertStringContainsString('Global Biofuel BV', $html);
        $this->assertStringContainsString('Alice Smith', $html);
        $this->assertStringContainsString('ISCC_EU_Certificate.pdf', $html);
    }
}
