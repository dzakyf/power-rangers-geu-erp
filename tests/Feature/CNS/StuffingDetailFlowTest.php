<?php

namespace Tests\Feature\CNS;

use App\User;
use App\VesselSchedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StuffingDetailFlowTest extends TestCase
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
    public function detail_page_renders_schedule_head_container_list_and_edit_form(): void
    {
        $orderNo = 'TEST-STUFF-DETAIL-' . uniqid();
        $vs = $this->makeVesselSchedule($orderNo);

        $response = $this->actingAs($this->testUser())
            ->get('/cns-stuffing-detail?task=' . $vs->id);

        $response->assertOk();
        $response->assertSee('VESSEL SCHEDULE');
        $response->assertSee('id="StuffingDetail"', false);
        $response->assertSee('id="StuffingContainerForm"', false);
        $response->assertSee('id="Order_No"', false);
        $response->assertSee('id="Time_From"', false);
        $response->assertSee('id="Container"', false);
        $response->assertSee('Add Container');
        $response->assertSee('Edit Container');
        $response->assertSee('Delete Container');
        $response->assertSee('Nota Timbang');
        $response->assertDontSee('addContainerModal');
        $response->assertDontSee('Submit Container');
    }

    /** @test */
    public function detail_xml_uses_monitoring_columns_and_note_indicator(): void
    {
        $orderNo = 'TEST-STUFF-XML-' . uniqid();
        $vs = $this->makeVesselSchedule($orderNo);
        $containerId = $this->makeContainer($vs, 'TESTU3000001');
        $this->makeNote($vs, 'note.pdf');

        $_SERVER['HTTP_ACCEPT'] = 'text/xml';

        ob_start();
        $response = $this->actingAs($this->testUser())
            ->get('/xml-cns-stuffing-detail?task=' . urlencode($vs->Task_ID) . '&has_checkbox=1');
        $content = ob_get_clean();

        $response->assertOk();
        $this->assertStringContainsString('<row id="' . $containerId . '"', $content);
        $this->assertStringContainsString('<cell>0</cell>', $content);
        $this->assertStringContainsString($orderNo, $content);
        $this->assertStringContainsString('TESTU3000001', $content);
        $this->assertStringContainsString('Ada', $content);
    }

    /** @test */
    public function detail_xml_hides_canceled_containers(): void
    {
        $orderNo = 'TEST-STUFF-CANCELED-' . uniqid();
        $vs = $this->makeVesselSchedule($orderNo);
        $this->makeContainer($vs, 'TESTU3000002');
        $this->makeContainer($vs, 'TESTU3000003', 'CANCELED');

        $_SERVER['HTTP_ACCEPT'] = 'text/xml';

        ob_start();
        $response = $this->actingAs($this->testUser())
            ->get('/xml-cns-stuffing-detail?task=' . urlencode($vs->Task_ID) . '&has_checkbox=1');
        $content = ob_get_clean();

        $response->assertOk();
        $this->assertStringContainsString('TESTU3000002', $content);
        $this->assertStringNotContainsString('TESTU3000003', $content);
    }

    /** @test */
    public function saving_a_container_recalculates_values_and_binds_to_the_schedule(): void
    {
        $orderNo = 'TEST-STUFF-SAVE-' . uniqid();
        $vs = $this->makeVesselSchedule($orderNo);

        $response = $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-save?task=' . $vs->id, [
                'Date' => '2026-11-02',
                'Time_From' => '08:00',
                'Time_To' => '09:00',
                'Police_No' => 'B 1234 TEST',
                'Container' => 'TESTU3000004',
                'Seal' => 'SEAL-SAVE',
                'Tare' => 2000,
                'Netto_KG' => 18000,
                'Netto_MT' => 999,
                'Gross_KG' => 999,
                'Gross_MT' => 999,
                'Note' => 'Saved from detail',
            ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);

        $row = DB::table('vessel_schedule_dtls')
            ->where('Task_ID', $vs->Task_ID)
            ->where('Container', 'TESTU3000004')
            ->first();

        $this->assertNotNull($row);
        $this->assertSame($orderNo, $row->Order_No);
        $this->assertSame('CLOSED', $row->Status);
        $this->assertSame(18.0, (float) $row->Netto_MT);
        $this->assertSame(20000.0, (float) $row->Gross_KG);
        $this->assertSame(20.0, (float) $row->Gross_MT);
        $this->assertSame(1, DB::table('vessel_schedule_logs')->where('Last_ID', $vs->id)->count());
    }

    /** @test */
    public function editing_rejects_a_container_from_another_schedule(): void
    {
        $first = $this->makeVesselSchedule('TEST-STUFF-OWNER-1-' . uniqid());
        $second = $this->makeVesselSchedule('TEST-STUFF-OWNER-2-' . uniqid());
        $foreignId = $this->makeContainer($second, 'TESTU3000005');

        $response = $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-save?task=' . $first->id, [
                'id' => $foreignId,
                'Date' => '2026-11-02',
                'Container' => 'TESTU3000005',
                'Tare' => 1000,
                'Netto_KG' => 1000,
            ]);

        $response->assertStatus(422);
        $this->assertSame($second->Task_ID, DB::table('vessel_schedule_dtls')->where('ID', $foreignId)->value('Task_ID'));
    }

    /** @test */
    public function batch_delete_soft_cancels_rows_clears_si_links_and_hides_them(): void
    {
        $orderNo = 'TEST-STUFF-DELETE-' . uniqid();
        $vs = $this->makeVesselSchedule($orderNo);
        $first = $this->makeContainer($vs, 'TESTU3000006', 'CLOSED', 'TEST-SI-OLD');
        $second = $this->makeContainer($vs, 'TESTU3000007');

        $response = $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-delete?task=' . $vs->id, [
                'ids' => [$first, $second],
            ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);

        $firstRow = DB::table('vessel_schedule_dtls')->where('ID', $first)->first();
        $this->assertSame('CANCELED', $firstRow->Status);
        $this->assertNull($firstRow->SI_No);
        $this->assertSame('CANCELED', DB::table('vessel_schedule_dtls')->where('ID', $second)->value('Status'));
        $this->assertSame(1, DB::table('vessel_schedule_logs')->where('Last_ID', $vs->id)->count());

        $_SERVER['HTTP_ACCEPT'] = 'text/xml';

        ob_start();
        $xml = $this->actingAs($this->testUser())
            ->get('/xml-cns-stuffing-detail?task=' . urlencode($vs->Task_ID) . '&has_checkbox=1');
        $content = ob_get_clean();

        $xml->assertOk();
        $this->assertStringNotContainsString('TESTU3000006', $content);
        $this->assertStringNotContainsString('TESTU3000007', $content);
    }

    /** @test */
    public function detail_data_returns_values_and_current_note(): void
    {
        $orderNo = 'TEST-STUFF-DATA-' . uniqid();
        $vs = $this->makeVesselSchedule($orderNo);
        $id = $this->makeContainer($vs, 'TESTU3000008', 'CLOSED', null, 'Data note');
        $this->makeNote($vs, 'data-note.pdf');

        $response = $this->actingAs($this->testUser())
            ->get('/cns-stuffing-detail-data?task=' . $vs->id . '&ID=' . $id);

        $response->assertOk();
        $response->assertJsonFragment([
            'ID' => $id,
            'Order_No' => $orderNo,
            'Container' => 'TESTU3000008',
            'Note' => 'Data note',
            'Note_Name' => 'data-note.pdf',
        ]);
    }

    /** @test */
    public function saving_a_container_can_replace_the_weighbridge_note(): void
    {
        $orderNo = 'TEST-STUFF-NOTE-' . uniqid();
        $vs = $this->makeVesselSchedule($orderNo);

        $response = $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-save?task=' . $vs->id, [
                'Date' => '2026-11-02',
                'Container' => 'TESTU3000009',
                'Tare' => 2000,
                'Netto_KG' => 18000,
                'Weighbridge_Note' => UploadedFile::fake()->create('detail-note.pdf', 100, 'application/pdf'),
            ]);

        $response->assertOk();

        $meta = DB::table('meta_files')
            ->where('Topic', self::NOTE_TOPIC)
            ->where('Map_To', (string) $vs->id)
            ->first();

        $this->assertNotNull($meta);
        $this->assertFileExists(public_path('filevendor/' . $meta->Path));
        $this->uploadedPaths[] = $meta->Path;

        $info = $this->actingAs($this->testUser())
            ->get('/cns-stuffing-detail-note-info?Order_No=' . urlencode($orderNo));

        $info->assertOk();
        $info->assertJson(['name' => $meta->Filename]);

        $file = $this->actingAs($this->testUser())
            ->get('/cns-stuffing-detail-note?Order_No=' . urlencode($orderNo));

        $file->assertOk();
    }

    private function makeVesselSchedule(string $orderNo): VesselSchedule
    {
        return VesselSchedule::create([
            'Task_ID' => 'VS/TEST/' . uniqid(),
            'Order_No' => $orderNo,
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
            'Created_By' => $this->testUser()->username,
            'Created_Date' => now(),
        ]);
    }

    private function makeContainer(
        VesselSchedule $vs,
        string $container,
        string $status = 'CLOSED',
        ?string $siNo = null,
        string $note = 'Test container'
    ): int {
        return DB::table('vessel_schedule_dtls')->insertGetId([
            'Task_ID' => $vs->Task_ID,
            'Order_No' => $vs->Order_No,
            'SI_No' => $siNo,
            'Container' => $container,
            'Seal' => 'SEAL-' . uniqid(),
            'Time_From' => '08:00:00',
            'Time_To' => '09:00:00',
            'Date' => '2026-11-02',
            'Police_No' => 'B 1234 TEST',
            'Tare' => 2000,
            'Netto_KG' => 18000,
            'Netto_MT' => 18,
            'Gross_KG' => 20000,
            'Gross_MT' => 20,
            'Note' => $note,
            'Status' => $status,
            'Created_By' => $this->testUser()->username,
            'Created_Date' => now(),
            'Updated_By' => $this->testUser()->username,
            'Updated_Date' => now(),
        ]);
    }

    private function makeNote(VesselSchedule $vs, string $filename): void
    {
        DB::table('meta_files')->insert([
            'Filename' => $filename,
            'Path' => '2026/11/' . $filename,
            'Extension' => 'pdf',
            'Description' => 'Weighbridge Note',
            'Topic' => self::NOTE_TOPIC,
            'Map_To' => (string) $vs->id,
        ]);
    }
}
