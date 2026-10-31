<?php

namespace Tests\Feature\CNS;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SupplierContractTest extends TestCase
{
    use DatabaseTransactions;

    /** @var User|null */
    private $user;

    private function testUser(): User
    {
        if ($this->user) {
            return $this->user;
        }

        $user = User::query()->orderBy('id')->first();

        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }

        return $this->user = $user;
    }

    /** @test */
    public function non_uco_vendor_is_saved_with_category_and_emails(): void
    {
        $payload = [
            'Company_Name' => 'CV UJI NON UCO',
            'Phone' => '08123456789',
            'Address' => 'Jl. Non UCO',
            'Emails' => ['a@example.com', 'b@example.com'],
            'Supply_Category' => 'Flexibag',
        ];

        $response = $this->actingAs($this->testUser())
            ->post('/cns-procurement-non-uco-supplier-new-submit', $payload);

        $response->assertOk();

        $row = DB::table('supplier')->where('Company_Name', 'CV UJI NON UCO')->first();

        $this->assertNotNull($row, 'Vendor row must be stored in supplier table');
        $this->assertSame('Flexibag', $row->Supply_Category);
        $this->assertSame('08123456789', $row->Phone);
        $this->assertSame('Jl. Non UCO', $row->Address);
        $this->assertSame(['a@example.com', 'b@example.com'], json_decode($row->Emails, true));
        $this->assertSame('a@example.com', $row->Email);
        $this->assertSame(1, (int) $row->Is_Supplier);
        $this->assertSame($this->testUser()->Location, $row->Location);
    }
    /** @test */
    public function uco_contract_is_saved_with_quantity_price_and_snapshot(): void
    {
        $buyerId = DB::table('supplier')->insertGetId([
            'Location'     => $this->testUser()->Location,
            'Supplier_ID'  => 'BUYER-UJI-001',
            'Company_Name' => 'BUYER UJI CONTRACT',
            'Address'      => 'Jl. Uji No. 1',
            'Phone'        => '081200000001',
            'Email'        => 'buyer@example.com',
            'Is_Consignee' => 1,
            'Is_Supplier'  => 0,
            'Status'       => 'NORMAL',
        ]);

        $payload = [
            'Contract_No'       => 'UJI-CTR-001',
            'Supplier_ID'       => $buyerId,
            'Buyer_Name'        => 'BUYER UJI CONTRACT',
            'Buyer_Address'     => 'Jl. Uji No. 1',
            'Buyer_Phone'       => '081200000001',
            'Buyer_Emails'      => ['buyer@example.com', 'ops@example.com'],
            'Quantity_MT'       => 500,
            'Container_Count'   => 25,
            'Container_Size'    => '20 ft',
            'Loading_Start'     => '2026-10-01',
            'Loading_End'       => '2026-10-30',
            'Incoterms'         => 'FOB',
            'Price_Currency'    => 'USD',
            'Price_Per_MT'      => 1105,
            'Status'            => 'UNDER_REVIEW',
            'Note'              => 'Catatan uji',
            'Contract_File'     => UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
        ];

        $response = $this->actingAs($this->testUser())
            ->post('/cns-procurement-buyer-contract-new-submit', $payload);

        $response->assertOk();

        $row = DB::table('cns_buyer_contracts')->where('Contract_No', 'UJI-CTR-001')->first();

        $this->assertNotNull($row, 'Contract row must be stored');
        $this->assertSame($buyerId, (int) $row->Supplier_ID);
        $this->assertSame('BUYER UJI CONTRACT', $row->Buyer_Name);
        $this->assertSame(['buyer@example.com', 'ops@example.com'], json_decode($row->Buyer_Emails, true));
        $this->assertSame('500.000', $row->Quantity_MT);
        $this->assertSame(25, (int) $row->Container_Count);
        $this->assertSame('20 ft', $row->Container_Size);
        $this->assertSame('2026-10-01', $row->Loading_Start);
        $this->assertSame('2026-10-30', $row->Loading_End);
        $this->assertSame('FOB', $row->Incoterms);
        $this->assertSame('USD', $row->Price_Currency);
        $this->assertSame('1105.00', $row->Price_Per_MT);
        $this->assertSame('UNDER_REVIEW', $row->Status);
        $this->assertSame($this->testUser()->username, $row->Created_By);
        $this->assertSame($this->testUser()->Location, $row->Location);
        $this->assertNotNull($row->Contract_File);

        $contract = \App\BuyerContract::find($row->ID);
        $this->assertSame('500 MT (25 x 20 ft)', $contract->quantityDisplay());
    }

    /** @test */
    public function booking_confirmation_is_added_to_contract(): void
    {
        $contractId = $this->makeContract('UJI-CTR-BOOK-001');

        $response = $this->actingAs($this->testUser())
            ->post('/cns-procurement-buyer-contract-booking-submit', [
                'Contract_ID'  => $contractId,
                'Label'        => 'A',
                'Booking_No'   => 'EGLV081600298008',
                'Booking_File' => UploadedFile::fake()->create('booking.pdf', 100, 'application/pdf'),
            ]);

        $response->assertOk();

        $row = DB::table('cns_buyer_contract_bookings')
            ->where('Contract_ID', $contractId)
            ->where('Label', 'A')
            ->first();

        $this->assertNotNull($row, 'Booking row must be stored');
        $this->assertSame('EGLV081600298008', $row->Booking_No);
        $this->assertNotNull($row->Booking_File);
        $this->assertSame($this->testUser()->username, $row->Created_By);
    }

    private function makeContract(string $contractNo): int
    {
        $buyerId = DB::table('supplier')->insertGetId([
            'Location'     => $this->testUser()->Location,
            'Supplier_ID'  => 'BUYER-' . $contractNo,
            'Company_Name' => 'BUYER ' . $contractNo,
            'Address'      => 'Jl. Uji',
            'Phone'        => '081200000002',
            'Email'        => 'buyer2@example.com',
            'Is_Consignee' => 1,
            'Is_Supplier'  => 0,
            'Status'       => 'NORMAL',
        ]);

        return DB::table('cns_buyer_contracts')->insertGetId([
            'Contract_No'     => $contractNo,
            'Supplier_ID'     => $buyerId,
            'Buyer_Name'      => 'BUYER ' . $contractNo,
            'Buyer_Address'   => 'Jl. Uji',
            'Buyer_Phone'     => '081200000002',
            'Buyer_Emails'    => json_encode(['buyer2@example.com']),
            'Quantity_MT'     => 100,
            'Container_Count' => 5,
            'Container_Size'  => '20 ft',
            'Loading_Start'   => '2026-10-01',
            'Loading_End'     => '2026-10-30',
            'Incoterms'       => 'FOB',
            'Price_Currency'  => 'USD',
            'Price_Per_MT'    => 1105,
            'Status'          => 'UNDER_REVIEW',
            'Location'        => $this->testUser()->Location,
            'Created_By'      => $this->testUser()->username,
        ]);
    }

    private function makeNonUcoSupplier(string $name, string $category = 'Flexibag'): int
    {
        return DB::table('supplier')->insertGetId([
            'Location'        => $this->testUser()->Location,
            'Supplier_ID'     => 'SUP-' . md5($name),
            'Company_Name'    => $name,
            'Address'         => 'Jl. ' . $name,
            'Phone'           => '081200009999',
            'Email'           => 'supplier@example.com',
            'Emails'          => json_encode(['supplier@example.com']),
            'Supply_Category' => $category,
            'Is_Company'      => 1,
            'Is_Supplier'     => 1,
            'Is_Consignee'    => 0,
            'Status'          => 'NORMAL',
            'Created_By'      => $this->testUser()->username,
            'Created_Date'    => now(),
        ]);
    }

    private function xmlContent(array $query = []): string
    {
        $url = '/xml-cns-procurement-buyer-contract' . ($query ? '?' . http_build_query($query) : '');

        ob_start();
        $this->actingAs($this->testUser())->get($url)->assertOk();

        return ob_get_clean();
    }

    /** @test */
    public function non_uco_vendor_rejects_more_than_ten_emails(): void
    {
        $emails = [];
        for ($i = 1; $i <= 11; $i++) {
            $emails[] = "user{$i}@example.com";
        }

        $response = $this->actingAs($this->testUser())
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
            ->post('/cns-procurement-non-uco-supplier-new-submit', [
                'Company_Name'    => 'CV EMAIL LEBIH',
                'Phone'           => '0812',
                'Emails'          => $emails,
                'Supply_Category' => 'Flexibag',
            ]);

        $response->assertStatus(422);
        $this->assertNull(DB::table('supplier')->where('Company_Name', 'CV EMAIL LEBIH')->first());
    }

    /** @test */
    public function contract_requires_a_contract_file(): void
    {
        $payload = $this->contractPayload('UJI-CTR-NOFILE');
        unset($payload['Contract_File']);

        $response = $this->actingAs($this->testUser())
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
            ->post('/cns-procurement-buyer-contract-new-submit', $payload);

        $response->assertStatus(422);
        $this->assertNull(DB::table('cns_buyer_contracts')->where('Contract_No', 'UJI-CTR-NOFILE')->first());
    }

    /** @test */
    public function contract_rejects_loading_end_before_start(): void
    {
        $payload = $this->contractPayload('UJI-CTR-DATE');
        $payload['Loading_Start'] = '2026-10-30';
        $payload['Loading_End'] = '2026-10-01';

        $response = $this->actingAs($this->testUser())
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
            ->post('/cns-procurement-buyer-contract-new-submit', $payload);

        $response->assertStatus(422);
        $this->assertNull(DB::table('cns_buyer_contracts')->where('Contract_No', 'UJI-CTR-DATE')->first());
    }

    /** @test */
    public function contract_rejects_supplier_that_is_not_a_consignee(): void
    {
        $vendorId = DB::table('supplier')->insertGetId([
            'Location'     => $this->testUser()->Location,
            'Supplier_ID'  => 'VENDOR-NOT-BUYER',
            'Company_Name' => 'VENDOR NOT BUYER',
            'Is_Consignee' => 0,
            'Is_Supplier'  => 1,
            'Status'       => 'NORMAL',
        ]);

        $payload = $this->contractPayload('UJI-CTR-NOTBUYER');
        $payload['Supplier_ID'] = $vendorId;
        $payload['Contract_No'] = 'UJI-CTR-NOTBUYER';

        $response = $this->actingAs($this->testUser())
            ->post('/cns-procurement-buyer-contract-new-submit', $payload);

        $response->assertStatus(422);
    }

    /** @test */
    public function booking_label_must_be_unique_per_contract(): void
    {
        $contractId = $this->makeContract('UJI-CTR-DUPLABEL');

        $payload = [
            'Contract_ID'  => $contractId,
            'Label'        => '1',
            'Booking_No'   => 'EGLV000000000001',
            'Booking_File' => UploadedFile::fake()->create('booking.pdf', 100, 'application/pdf'),
        ];

        $this->actingAs($this->testUser())
            ->post('/cns-procurement-buyer-contract-booking-submit', $payload)
            ->assertOk();

        $second = $this->actingAs($this->testUser())
            ->post('/cns-procurement-buyer-contract-booking-submit', $payload);

        $second->assertStatus(422);
        $this->assertSame(1, DB::table('cns_buyer_contract_bookings')->where('Contract_ID', $contractId)->count());
    }

    private function contractPayload(string $contractNo): array
    {
        $buyerId = DB::table('supplier')->insertGetId([
            'Location'     => $this->testUser()->Location,
            'Supplier_ID'  => 'BUYER-' . $contractNo,
            'Company_Name' => 'BUYER ' . $contractNo,
            'Address'      => 'Jl. Uji',
            'Phone'        => '081200000003',
            'Email'        => 'buyer3@example.com',
            'Is_Consignee' => 1,
            'Is_Supplier'  => 0,
            'Status'       => 'NORMAL',
        ]);

        return [
            'Contract_No'      => $contractNo,
            'Supplier_ID'      => $buyerId,
            'Buyer_Name'       => 'BUYER ' . $contractNo,
            'Buyer_Address'    => 'Jl. Uji',
            'Buyer_Phone'      => '081200000003',
            'Buyer_Emails'     => ['buyer3@example.com'],
            'Quantity_MT'      => 100,
            'Container_Count'  => 5,
            'Container_Size'   => '20 ft',
            'Loading_Start'    => '2026-10-01',
            'Loading_End'      => '2026-10-30',
            'Incoterms'        => 'FOB',
            'Price_Currency'   => 'USD',
            'Price_Per_MT'     => 1105,
            'Status'           => 'UNDER_REVIEW',
            'Contract_File'    => UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
        ];
    }

    /** @test */
    public function contract_pages_render(): void
    {
        $this->actingAs($this->testUser())->get('/cns-procurement-buyer-contract')->assertOk();
        $this->actingAs($this->testUser())->get('/cns-procurement-buyer-contract-new')->assertOk();
        ob_start();
        $this->actingAs($this->testUser())->get('/xml-cns-procurement-buyer-contract')->assertOk();
        ob_end_clean();

        $contractId = $this->makeContract('UJI-CTR-DETAIL-PAGE');
        $this->actingAs($this->testUser())
            ->get('/cns-procurement-buyer-contract-detail?ID=' . $contractId)
            ->assertOk();
    }

    /** @test */
    public function non_uco_detail_page_renders(): void
    {
        $vendorId = $this->makeNonUcoSupplier('VENDOR DETAIL PAGE');

        $this->actingAs($this->testUser())
            ->get('/cns-procurement-non-uco-supplier-detail?ID=' . $vendorId)
            ->assertOk();
    }

    /** @test */
    public function buyers_endpoint_returns_consignee_data(): void
    {
        $buyerId = DB::table('supplier')->insertGetId([
            'Location'     => $this->testUser()->Location,
            'Supplier_ID'  => 'BUYER-JSON-001',
            'Company_Name' => 'BUYER JSON',
            'Address'      => 'Jl. Json',
            'Phone'        => '0812777',
            'Email'        => 'json1@example.com',
            'Emails'       => json_encode(['json1@example.com', 'json2@example.com']),
            'Is_Consignee' => 1,
            'Is_Supplier'  => 0,
            'Status'       => 'NORMAL',
        ]);

        $response = $this->actingAs($this->testUser())
            ->get('/cns-procurement-buyer-contract-buyers');

        $response->assertOk();
        $response->assertJsonFragment([
            'id'   => $buyerId,
            'name' => 'BUYER JSON',
        ]);

        $buyer = collect($response->json())->firstWhere('id', $buyerId);
        $this->assertSame(['json1@example.com', 'json2@example.com'], $buyer['emails']);
    }

    /** @test */
    public function non_uco_vendor_can_be_updated(): void
    {
        $vendorId = DB::table('supplier')->insertGetId([
            'Location'        => $this->testUser()->Location,
            'Supplier_ID'     => 'GEU-COMP-2-UPD',
            'Company_Name'    => 'VENDOR LAMA',
            'Phone'           => '0811',
            'Address'         => 'Jl. Lama',
            'Email'           => 'lama@example.com',
            'Emails'          => json_encode(['lama@example.com']),
            'Supply_Category' => 'Flexibag',
            'Is_Supplier'     => 1,
            'Status'          => 'NORMAL',
        ]);

        $response = $this->actingAs($this->testUser())
            ->post('/cns-procurement-non-uco-supplier-detail-submit', [
                'ID'              => $vendorId,
                'Company_Name'    => 'VENDOR BARU',
                'Phone'           => '0822',
                'Address'         => 'Jl. Baru',
                'Emails'          => ['baru@example.com', 'kedua@example.com'],
                'Supply_Category' => 'Besi Penyangga',
            ]);

        $response->assertOk();

        $row = DB::table('supplier')->where('ID', $vendorId)->first();
        $this->assertSame('VENDOR BARU', $row->Company_Name);
        $this->assertSame('0822', $row->Phone);
        $this->assertSame('Jl. Baru', $row->Address);
        $this->assertSame('baru@example.com', $row->Email);
        $this->assertSame(['baru@example.com', 'kedua@example.com'], json_decode($row->Emails, true));
        $this->assertSame('Besi Penyangga', $row->Supply_Category);
    }

    /** @test */
    public function contract_can_be_updated_without_replacing_the_file(): void
    {
        $contractId = $this->makeContract('UJI-CTR-UPD');
        $contract = DB::table('cns_buyer_contracts')->where('ID', $contractId)->first();

        $response = $this->actingAs($this->testUser())
            ->post('/cns-procurement-buyer-contract-detail-submit', [
                'ID'              => $contractId,
                'Contract_No'     => 'UJI-CTR-UPDATED',
                'Supplier_ID'     => $contract->Supplier_ID,
                'Buyer_Name'      => 'BUYER UPDATED',
                'Buyer_Address'   => 'Jl. Baru',
                'Buyer_Phone'     => '0812999',
                'Buyer_Emails'    => ['updated@example.com'],
                'Quantity_MT'     => 750,
                'Container_Count' => 30,
                'Container_Size'  => '40 ft',
                'Loading_Start'   => '2026-11-01',
                'Loading_End'     => '2026-11-30',
                'Incoterms'       => 'CIF',
                'Price_Currency'  => 'EUR',
                'Price_Per_MT'    => 1200,
                'Status'          => 'ACTIVE_IN_PROGRESS',
                'Note'            => 'Update catatan',
            ]);

        $response->assertOk();

        $row = DB::table('cns_buyer_contracts')->where('ID', $contractId)->first();
        $this->assertSame('UJI-CTR-UPDATED', $row->Contract_No);
        $this->assertSame('BUYER UPDATED', $row->Buyer_Name);
        $this->assertSame('40 ft', $row->Container_Size);
        $this->assertSame('CIF', $row->Incoterms);
        $this->assertSame('EUR', $row->Price_Currency);
        $this->assertSame('ACTIVE_IN_PROGRESS', $row->Status);
        $this->assertSame(['updated@example.com'], json_decode($row->Buyer_Emails, true));
        $this->assertSame($this->testUser()->username, $row->Updated_By);
        $this->assertNull($row->Contract_File, 'Old file should remain untouched when no new file is uploaded');
    }

    /** @test */
    public function booking_can_be_deleted(): void
    {
        $contractId = $this->makeContract('UJI-CTR-DELBOOK');

        $this->actingAs($this->testUser())
            ->post('/cns-procurement-buyer-contract-booking-submit', [
                'Contract_ID'  => $contractId,
                'Label'        => 'B',
                'Booking_No'   => 'EGLV000000000002',
                'Booking_File' => UploadedFile::fake()->create('booking.pdf', 100, 'application/pdf'),
            ])->assertOk();

        $bookingId = DB::table('cns_buyer_contract_bookings')
            ->where('Contract_ID', $contractId)
            ->value('ID');

        $this->assertNotNull($bookingId);

        $this->actingAs($this->testUser())
            ->post('/cns-procurement-buyer-contract-booking-delete', [
                'ID'          => $bookingId,
                'Contract_ID' => $contractId,
            ])->assertOk();

        $this->assertSame(0, DB::table('cns_buyer_contract_bookings')->where('Contract_ID', $contractId)->count());
    }


    /** @test */
    public function unified_procurement_list_contains_contract_and_non_uco_supplier(): void
    {
        $this->makeContract('UJI-LIST-CONTRACT');
        $this->makeNonUcoSupplier('VENDOR LIST NON UCO');

        $xml = $this->xmlContent();

        $this->assertStringContainsString('UJI-LIST-CONTRACT', $xml);
        $this->assertStringContainsString('VENDOR LIST NON UCO', $xml);
        $this->assertStringContainsString('Non-UCO', $xml);
    }

    /** @test */
    public function unified_procurement_list_filters_by_contract_group_and_product(): void
    {
        $this->makeContract('UJI-FILTER-CONTRACT');
        $this->makeNonUcoSupplier('VENDOR FILTER FLEXIBAG', 'Flexibag');

        $with = $this->xmlContent(['contract' => 'with']);
        $this->assertStringContainsString('UJI-FILTER-CONTRACT', $with);
        $this->assertStringNotContainsString('VENDOR FILTER FLEXIBAG', $with);

        $without = $this->xmlContent(['contract' => 'without']);
        $this->assertStringContainsString('VENDOR FILTER FLEXIBAG', $without);
        $this->assertStringNotContainsString('UJI-FILTER-CONTRACT', $without);

        $uco = $this->xmlContent(['group' => 'UCO']);
        $this->assertStringContainsString('UJI-FILTER-CONTRACT', $uco);
        $this->assertStringNotContainsString('VENDOR FILTER FLEXIBAG', $uco);

        $nonUco = $this->xmlContent(['group' => 'Non-UCO']);
        $this->assertStringContainsString('VENDOR FILTER FLEXIBAG', $nonUco);
        $this->assertStringNotContainsString('UJI-FILTER-CONTRACT', $nonUco);

        $product = $this->xmlContent(['product' => 'Flexibag']);
        $this->assertStringContainsString('VENDOR FILTER FLEXIBAG', $product);
        $this->assertStringNotContainsString('UJI-FILTER-CONTRACT', $product);
    }
}
