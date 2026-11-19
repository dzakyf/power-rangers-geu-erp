<?php

namespace Tests\Feature\CNS;

use App\User;
use App\VesselSchedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StuffingBookingConfirmationTest extends TestCase
{
    use DatabaseTransactions;

    private const TOPIC = 'cns-booking-confirmation';

    /** @var User|null */
    private $user;

    /** @var array<int, string> */
    private $uploadedPaths = [];

    protected function tearDown(): void
    {
        foreach ($this->uploadedPaths as $path) {
            $fullPath = public_path('filevendor/' . $path);
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        parent::tearDown();
    }

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
    public function stuffing_detail_renders_the_booking_confirmation_card_and_contract_quantity(): void
    {
        $contract = $this->makeContract(36);
        $vs = $this->makeVesselSchedule($contract->ID, $contract->Contract_No);

        $response = $this->actingAs($this->testUser())
            ->get('/cns-stuffing-detail?task=' . $vs->id);

        $response->assertOk();
        $response->assertSee('BOOKING CONFIRMATION');
        $response->assertSee('id="BookingConfirmationForm"', false);
        $response->assertSee('id="BC_File"', false);
        $response->assertSee('id="BC_No"', false);
        $response->assertSee('id="BC_Status"', false);
        $response->assertSee('id="BC_Note"', false);
        $response->assertSee('id="SaveBookingConfirmation"', false);
        $response->assertSee('36.000');
    }

    /** @test */
    public function uploading_a_bc_stores_meta_files_and_links_the_schedule(): void
    {
        $contract = $this->makeContract(36);
        $vs = $this->makeVesselSchedule($contract->ID, $contract->Contract_No);

        $response = $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-booking-confirmation?task=' . $vs->id, [
                'BC_File' => UploadedFile::fake()->create('booking-confirmation.pdf', 100, 'application/pdf'),
                'BC_No' => 'BC-TEST-001',
            ]);

        $response->assertOk()->assertJson(['status' => 'success']);

        $meta = DB::table('meta_files')
            ->where('Topic', self::TOPIC)
            ->where('Map_To', (string) $vs->id)
            ->first();

        $this->assertNotNull($meta);
        $this->assertFileExists(public_path('filevendor/' . $meta->Path));
        $this->uploadedPaths[] = $meta->Path;

        $this->assertSame((string) $meta->ID, (string) DB::table('vessel_schedules')->where('id', $vs->id)->value('BC_Meta_File_ID'));
        $this->assertSame('BC-TEST-001', DB::table('vessel_schedules')->where('id', $vs->id)->value('BC_No'));
    }

    /** @test */
    public function replacing_a_bc_removes_the_previous_meta_file_and_physical_file(): void
    {
        $contract = $this->makeContract(36);
        $vs = $this->makeVesselSchedule($contract->ID, $contract->Contract_No);

        $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-booking-confirmation?task=' . $vs->id, [
                'BC_File' => UploadedFile::fake()->create('booking-confirmation-old.pdf', 100, 'application/pdf'),
                'BC_No' => 'BC-TEST-001',
            ])
            ->assertOk();

        $old = DB::table('meta_files')->where('Topic', self::TOPIC)->where('Map_To', (string) $vs->id)->first();
        $oldPath = public_path('filevendor/' . $old->Path);
        $this->uploadedPaths[] = $old->Path;

        $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-booking-confirmation?task=' . $vs->id, [
                'BC_File' => UploadedFile::fake()->create('booking-confirmation-new.pdf', 100, 'application/pdf'),
                'BC_No' => 'BC-TEST-002',
            ])
            ->assertOk();

        $new = DB::table('meta_files')->where('Topic', self::TOPIC)->where('Map_To', (string) $vs->id)->first();
        $this->assertNotSame($old->ID, $new->ID);
        $this->assertNull(DB::table('meta_files')->where('ID', $old->ID)->first());
        $this->assertFalse(is_file($oldPath));
        $this->uploadedPaths[] = $new->Path;
    }

    /** @test */
    public function saving_manual_assessment_status_and_note_records_them(): void
    {
        $contract = $this->makeContract(36);
        $vs = $this->makeVesselSchedule($contract->ID, $contract->Contract_No);

        $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-booking-confirmation?task=' . $vs->id, [
                'BC_File' => UploadedFile::fake()->create('booking-confirmation.pdf', 100, 'application/pdf'),
                'BC_No' => 'BC-TEST-003',
            ])
            ->assertOk();

        $meta = DB::table('meta_files')->where('Topic', self::TOPIC)->where('Map_To', (string) $vs->id)->first();
        $this->uploadedPaths[] = $meta->Path;

        $response = $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-booking-confirmation?task=' . $vs->id, [
                'BC_Status' => 'SESUAI',
                'BC_Note' => 'Qty BC sesuai dengan kontrak.',
            ]);

        $response->assertOk();

        $schedule = DB::table('vessel_schedules')->where('id', $vs->id)->first();
        $this->assertSame('SESUAI', $schedule->BC_Status);
        $this->assertSame('Qty BC sesuai dengan kontrak.', $schedule->BC_Note);
        $this->assertSame($this->testUser()->username, $schedule->BC_Updated_By);
        $this->assertNotNull($schedule->BC_Updated_Date);
        $this->assertSame(2, DB::table('vessel_schedule_logs')->where('Last_ID', $vs->id)->count());
    }

    /** @test */
    public function first_bc_save_requires_a_file_and_validates_file_type_and_status(): void
    {
        $contract = $this->makeContract(36);
        $vs = $this->makeVesselSchedule($contract->ID, $contract->Contract_No);

        $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-booking-confirmation?task=' . $vs->id, [])
            ->assertStatus(422);

        $this->actingAs($this->testUser())
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/cns-stuffing-detail-booking-confirmation?task=' . $vs->id, [
                'BC_File' => UploadedFile::fake()->create('booking-confirmation.pdf', 100, 'application/pdf'),
            ])
            ->assertStatus(422);

        $this->actingAs($this->testUser())
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/cns-stuffing-detail-booking-confirmation?task=' . $vs->id, [
                'BC_File' => UploadedFile::fake()->create('booking-confirmation.docx', 100),
                'BC_No' => 'BC-TEST-005',
            ])
            ->assertStatus(422);

        $this->actingAs($this->testUser())
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/cns-stuffing-detail-booking-confirmation?task=' . $vs->id, [
                'BC_File' => UploadedFile::fake()->create('booking-confirmation.pdf', 100, 'application/pdf'),
                'BC_No' => 'BC-TEST-004',
                'BC_Status' => 'INVALID',
            ])
            ->assertStatus(422);
    }

    private function makeContract(float $quantity)
    {
        $user = $this->testUser();

        $id = DB::table('cns_buyer_contracts')->insertGetId([
            'Contract_No' => 'TEST-BC-' . uniqid(),
            'Supplier_ID' => null,
            'Buyer_Name' => 'Test Buyer',
            'Buyer_Address' => 'Test Address',
            'Buyer_Phone' => '000',
            'Buyer_Emails' => json_encode(['buyer@example.test']),
            'Quantity_MT' => $quantity,
            'Container_Count' => 2,
            'Container_Size' => '20 ft',
            'Incoterms' => 'FOB',
            'Price_Currency' => 'USD',
            'Price_Per_MT' => 1000,
            'Status' => 'ACTIVE_IN_PROGRESS',
            'Location' => $user->Location,
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);

        return DB::table('cns_buyer_contracts')->where('ID', $id)->first();
    }

    private function makeVesselSchedule($contractId, string $contractNo): VesselSchedule
    {
        $user = $this->testUser();

        return VesselSchedule::create([
            'Task_ID' => 'VS/TEST/' . uniqid(),
            'Order_No' => 'TEST-BC-' . uniqid(),
            'Contract_ID' => $contractId,
            'Contract_No' => $contractNo,
            'Shipping_Method' => 'CONTAINER',
            'Qty' => '2 x 20 FT',
            'Qty1' => '2',
            'Qty2' => '20 FT',
            'Vessel' => 'TEST VESSEL',
            'POL' => 'TEST POL',
            'POD' => 'TEST POD',
            'Stuffing' => '2026-11-02',
            'Checking_BC' => '2026-11-03',
            'Closing_CY' => '2026-11-04',
            'ETD' => '2026-11-05',
            'ETA' => '2026-11-06',
            'Note' => 'Test schedule',
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);
    }
}
