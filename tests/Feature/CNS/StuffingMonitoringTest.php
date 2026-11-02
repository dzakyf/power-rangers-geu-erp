<?php

namespace Tests\Feature\CNS;

use App\User;
use App\VesselSchedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StuffingMonitoringTest extends TestCase
{
    use DatabaseTransactions;

    private const NOTE_TOPIC = 'stuffing-weighbridge-note';

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
    public function monitoring_page_renders_the_form_and_action_buttons(): void
    {
        $response = $this->actingAs($this->testUser())
            ->get('/cns-export-stuffing-monitoring');

        $response->assertOk();
        $response->assertSee('BUAT FORM STUFFING MONITORING');
        $response->assertSee('Edit Container');
        $response->assertSee('Upload Nota Timbang');
        $response->assertDontSee('Update Data');
    }

    /** @test */
    public function saving_a_new_container_calculates_net_and_gross_values(): void
    {
        $orderNo = 'TEST-MON-NEW-' . uniqid();
        $this->makeVesselSchedule($orderNo);

        $response = $this->actingAs($this->testUser())
            ->post('/cns-export-stuffing-monitoring-save', [
                'Order_No' => $orderNo,
                'Time_From' => '08:00',
                'Time_To' => '09:00',
                'Date' => '2026-10-01',
                'Police_No' => 'B 1234 TEST',
                'Container' => 'TESTU1000001',
                'Seal' => 'SEAL-001',
                'Tare' => 2000,
                'Netto_KG' => 18000,
                'Netto_MT' => 999,
                'Gross_KG' => 999,
                'Gross_MT' => 999,
                'Note' => 'Test note',
            ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);

        $row = DB::table('vessel_schedule_dtls')
            ->where('Order_No', $orderNo)
            ->where('Container', 'TESTU1000001')
            ->first();

        $this->assertNotNull($row);
        $this->assertSame(18.0, (float) $row->Netto_MT);
        $this->assertSame(20000.0, (float) $row->Gross_KG);
        $this->assertSame(20.0, (float) $row->Gross_MT);
    }

    /** @test */
    public function editing_a_container_recalculates_values_even_when_stale_values_are_posted(): void
    {
        $orderNo = 'TEST-MON-EDIT-' . uniqid();
        $vs = $this->makeVesselSchedule($orderNo);
        $id = $this->makeContainer($vs, 'TESTU1000002', 3100, 9000, 1, 1, 1);

        $response = $this->actingAs($this->testUser())
            ->post('/cns-export-stuffing-monitoring-save', [
                'id' => $id,
                'Order_No' => $orderNo,
                'Time_From' => '10:00',
                'Time_To' => '11:00',
                'Date' => '2026-10-02',
                'Police_No' => 'B 5678 TEST',
                'Container' => 'TESTU1000002',
                'Seal' => 'SEAL-002',
                'Tare' => 1500,
                'Netto_KG' => 12500,
                'Netto_MT' => 999,
                'Gross_KG' => 999,
                'Gross_MT' => 999,
                'Note' => 'Edited note',
            ]);

        $response->assertOk();

        $row = DB::table('vessel_schedule_dtls')->where('ID', $id)->first();

        $this->assertSame(12.5, (float) $row->Netto_MT);
        $this->assertSame(14000.0, (float) $row->Gross_KG);
        $this->assertSame(14.0, (float) $row->Gross_MT);
        $this->assertSame('Edited note', $row->Note);
        $this->assertSame(1, DB::table('vessel_schedule_dtls')->where('Order_No', $orderNo)->count());
    }

    /** @test */
    public function detail_endpoint_returns_container_values_for_editing(): void
    {
        $orderNo = 'TEST-MON-DETAIL-' . uniqid();
        $vs = $this->makeVesselSchedule($orderNo);
        $id = $this->makeContainer($vs, 'TESTU1000008', 2000, 12000, 1, 1, 1, 'Detail note');

        DB::table('meta_files')->insert([
            'Filename' => 'note.pdf',
            'Path' => '2026/11/note.pdf',
            'Extension' => 'pdf',
            'Description' => 'Weighbridge Note',
            'Topic' => self::NOTE_TOPIC,
            'Map_To' => (string) $vs->id,
        ]);

        $response = $this->actingAs($this->testUser())
            ->get('/cns-export-stuffing-monitoring-detail?ID=' . $id);

        $response->assertOk();
        $response->assertJsonFragment([
            'ID' => $id,
            'Order_No' => $orderNo,
            'Time_From' => '08:00',
            'Time_To' => '09:00',
            'Note' => 'Detail note',
        ]);
        $this->assertNotNull($response->json('Note_Url'));
    }

    /** @test */
    public function note_info_endpoint_returns_the_existing_note_for_an_order(): void
    {
        $orderNo = 'TEST-MON-NOTE-INFO-' . uniqid();
        $vs = $this->makeVesselSchedule($orderNo);

        DB::table('meta_files')->insert([
            'Filename' => 'note.pdf',
            'Path' => '2026/11/note.pdf',
            'Extension' => 'pdf',
            'Description' => 'Weighbridge Note',
            'Topic' => self::NOTE_TOPIC,
            'Map_To' => (string) $vs->id,
        ]);

        $response = $this->actingAs($this->testUser())
            ->get('/cns-export-stuffing-monitoring-note-info?Order_No=' . urlencode($orderNo));

        $response->assertOk();
        $response->assertJson([
            'name' => 'note.pdf',
        ]);
        $this->assertNotNull($response->json('url'));
    }

    /** @test */
    public function saving_a_container_stores_weighbridge_note_metadata_and_file(): void
    {
        $orderNo = 'TEST-MON-UPLOAD-' . uniqid();
        $vs = $this->makeVesselSchedule($orderNo);

        $response = $this->actingAs($this->testUser())
            ->post('/cns-export-stuffing-monitoring-save', [
                'Order_No' => $orderNo,
                'Date' => '2026-10-01',
                'Container' => 'TESTU1000003',
                'Tare' => 2000,
                'Netto_KG' => 18000,
                'Weighbridge_Note' => UploadedFile::fake()->create('note.pdf', 100, 'application/pdf'),
            ]);

        $response->assertOk();

        $meta = DB::table('meta_files')
            ->where('Topic', self::NOTE_TOPIC)
            ->where('Map_To', (string) $vs->id)
            ->first();

        $this->assertNotNull($meta);
        $this->assertSame('pdf', $meta->Extension);
        $this->assertFileExists(public_path('filevendor/' . $meta->Path));

        $this->uploadedPaths[] = $meta->Path;

        $view = $this->actingAs($this->testUser())
            ->get('/cns-export-stuffing-monitoring-note?Order_No=' . urlencode($orderNo));

        $view->assertOk();
    }

    /** @test */
    public function deleting_a_container_keeps_the_order_weighbridge_note(): void
    {
        $orderNo = 'TEST-MON-DELETE-' . uniqid();
        $vs = $this->makeVesselSchedule($orderNo);
        $id = $this->makeContainer($vs, 'TESTU1000004', 2000, 18000);

        DB::table('meta_files')->insert([
            'Filename' => 'note.pdf',
            'Path' => '2026/11/note.pdf',
            'Extension' => 'pdf',
            'Description' => 'Weighbridge Note',
            'Topic' => self::NOTE_TOPIC,
            'Map_To' => (string) $vs->id,
        ]);

        $response = $this->actingAs($this->testUser())
            ->post('/cns-export-stuffing-monitoring-delete', ['id' => $id]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);

        $this->assertSame('CANCELED', DB::table('vessel_schedule_dtls')->where('ID', $id)->value('Status'));
        $this->assertNotNull(
            DB::table('meta_files')->where('Topic', self::NOTE_TOPIC)->where('Map_To', (string) $vs->id)->first()
        );
    }

    /** @test */
    public function monitoring_xml_hides_canceled_containers(): void
    {
        $orderNo = 'TEST-MON-HIDE-' . uniqid();
        $vs = $this->makeVesselSchedule($orderNo);
        $this->makeContainer($vs, 'TESTU1000009');
        $this->makeContainer($vs, 'TESTU1000010', 2000, 18000, 99, 99, 99, 'Canceled', 'CANCELED');

        $_SERVER['HTTP_ACCEPT'] = 'text/xml';

        ob_start();
        $response = $this->actingAs($this->testUser())
            ->get('/xml-cns-export-stuffing-monitoring?tgl1=2026-10-01&tgl2=2026-10-01');
        $content = ob_get_clean();

        $response->assertOk();
        $this->assertStringContainsString('TESTU1000009', $content);
        $this->assertStringNotContainsString('TESTU1000010', $content);
    }

    /** @test */
    public function monitoring_xml_includes_note_and_calculated_values(): void
    {
        $orderNo = 'TEST-MON-XML-' . uniqid();
        $vs = $this->makeVesselSchedule($orderNo);
        $this->makeContainer($vs, 'TESTU1000005', 2000, 12000, 1, 1, 1, 'XML note');

        DB::table('meta_files')->insert([
            'Filename' => 'note.pdf',
            'Path' => '2026/11/note.pdf',
            'Extension' => 'pdf',
            'Description' => 'Weighbridge Note',
            'Topic' => self::NOTE_TOPIC,
            'Map_To' => (string) $vs->id,
        ]);

        $_SERVER['HTTP_ACCEPT'] = 'text/xml';

        ob_start();
        $response = $this->actingAs($this->testUser())
            ->get('/xml-cns-export-stuffing-monitoring?tgl1=2026-10-01&tgl2=2026-10-01');
        $content = ob_get_clean();

        $response->assertOk();
        $this->assertStringContainsString('XML note', $content);
        $this->assertStringContainsString('12.00', $content);
        $this->assertStringContainsString('14000.00', $content);
        $this->assertStringContainsString('14.00', $content);
        $this->assertStringContainsString('Ada', $content);
    }

    /** @test */
    public function weighbridge_note_rejects_invalid_file_type(): void
    {
        $orderNo = 'TEST-MON-TYPE-' . uniqid();
        $this->makeVesselSchedule($orderNo);

        $response = $this->actingAs($this->testUser())
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/cns-export-stuffing-monitoring-save', [
                'Order_No' => $orderNo,
                'Date' => '2026-10-01',
                'Container' => 'TESTU1000006',
                'Weighbridge_Note' => UploadedFile::fake()->create('note.docx', 100),
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function weighbridge_note_rejects_files_larger_than_eight_megabytes(): void
    {
        $orderNo = 'TEST-MON-SIZE-' . uniqid();
        $this->makeVesselSchedule($orderNo);

        $response = $this->actingAs($this->testUser())
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/cns-export-stuffing-monitoring-save', [
                'Order_No' => $orderNo,
                'Date' => '2026-10-01',
                'Container' => 'TESTU1000007',
                'Weighbridge_Note' => UploadedFile::fake()->create('large.pdf', 9000, 'application/pdf'),
            ]);

        $response->assertStatus(422);
    }

    private function makeVesselSchedule(string $orderNo): VesselSchedule
    {
        return VesselSchedule::create([
            'Task_ID' => 'VS/TEST/' . uniqid(),
            'Order_No' => $orderNo,
            'Qty' => '1 x 20 FT',
            'Qty1' => '1',
            'Qty2' => '20 FT',
            'Vessel' => 'TEST VESSEL',
            'POL' => 'TEST POL',
            'POD' => 'TEST POD',
            'Stuffing' => '2026-10-01',
            'Checking_BC' => '2026-10-02',
            'Closing_CY' => '2026-10-03',
            'ETD' => '2026-10-04',
            'ETA' => '2026-10-05',
            'Note' => 'Test schedule',
            'Created_By' => $this->testUser()->username,
            'Created_Date' => now(),
        ]);
    }

    private function makeContainer(
        VesselSchedule $vs,
        string $container,
        float $tare = 2000,
        float $netto = 18000,
        float $nettoMt = 99,
        float $grossKg = 99,
        float $grossMt = 99,
        string $note = 'Test container',
        string $status = 'CLOSED'
    ): int {
        return DB::table('vessel_schedule_dtls')->insertGetId([
            'Task_ID' => $vs->Task_ID,
            'Order_No' => $vs->Order_No,
            'Container' => $container,
            'Seal' => 'SEAL-TEST',
            'Time_From' => '08:00:00',
            'Time_To' => '09:00:00',
            'Date' => '2026-10-01',
            'Police_No' => 'B 1234 TEST',
            'Tare' => $tare,
            'Netto_KG' => $netto,
            'Netto_MT' => $nettoMt,
            'Gross_KG' => $grossKg,
            'Gross_MT' => $grossMt,
            'Note' => $note,
            'Status' => $status,
            'Created_By' => $this->testUser()->username,
            'Created_Date' => now(),
            'Updated_By' => $this->testUser()->username,
            'Updated_Date' => now(),
        ]);
    }
}
