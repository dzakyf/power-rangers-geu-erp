<?php

namespace Tests\Feature\CNS;

use App\User;
use App\VesselSchedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StuffingPhotoChecklistTest extends TestCase
{
    use DatabaseTransactions;

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
    public function it_stores_all_container_photo_categories_against_the_container(): void
    {
        $vs = $this->makeVesselSchedule();

        $response = $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-save?task=' . $vs->id, array_merge([
                'Date' => '2026-11-02',
                'Container' => 'TESTU-PHOTO-1',
                'Tare' => 2000,
                'Netto_KG' => 18000,
                'Flexibag_Photos' => [
                    $this->image('flex-1.jpg'),
                    $this->image('flex-2.jpg'),
                    $this->image('flex-3.jpg'),
                    $this->image('flex-4.jpg'),
                    $this->image('flex-5.jpg'),
                ],
            ], $this->singlePhotoPayload()));

        $response->assertOk()->assertJson(['status' => 'success']);

        $container = DB::table('vessel_schedule_dtls')->where('Container', 'TESTU-PHOTO-1')->first();
        $this->assertNotNull($container);
        $this->rememberContainerFiles($container->ID);

        $this->assertSame(5, DB::table('meta_files')->where('Topic', 'stuffing-flexibag-photo')->where('Map_To', (string) $container->ID)->count());

        foreach ($this->singleTopics() as $topic) {
            $this->assertSame(
                1,
                DB::table('meta_files')->where('Topic', $topic)->where('Map_To', (string) $container->ID)->count(),
                "Missing photo metadata for {$topic}."
            );
        }
    }

    /** @test */
    public function it_keeps_the_order_weighbridge_note_separate_from_container_photos(): void
    {
        $vs = $this->makeVesselSchedule();

        $response = $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-save?task=' . $vs->id, [
                'Date' => '2026-11-02',
                'Container' => 'TESTU-PHOTO-2',
                'Tare' => 2000,
                'Netto_KG' => 18000,
                'Weighbridge_Note' => $this->image('order-note.jpg'),
                'Weighbridge_Photo' => $this->image('container-note.jpg'),
            ]);

        $response->assertOk();

        $container = DB::table('vessel_schedule_dtls')->where('Container', 'TESTU-PHOTO-2')->first();
        $this->rememberContainerFiles($container->ID);

        $orderNote = DB::table('meta_files')->where('Topic', 'stuffing-weighbridge-note')->where('Map_To', (string) $vs->id)->first();
        $containerPhoto = DB::table('meta_files')->where('Topic', 'stuffing-container-weighbridge-photo')->where('Map_To', (string) $container->ID)->first();

        $this->assertNotNull($orderNote);
        $this->assertNotNull($containerPhoto);
    }

    /** @test */
    public function it_rejects_more_than_seven_total_flexibag_photos(): void
    {
        $vs = $this->makeVesselSchedule();
        $this->createContainerWithFlexibag($vs, 'TESTU-PHOTO-3', 7);

        $container = DB::table('vessel_schedule_dtls')->where('Container', 'TESTU-PHOTO-3')->first();

        $response = $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-save?task=' . $vs->id, [
                'id' => $container->ID,
                'Date' => '2026-11-02',
                'Container' => 'TESTU-PHOTO-3',
                'Tare' => 2000,
                'Netto_KG' => 18000,
                'Flexibag_Photos' => [$this->image('flex-extra.jpg')],
            ]);

        $response->assertStatus(422);
        $this->assertSame(7, DB::table('meta_files')->where('Topic', 'stuffing-flexibag-photo')->where('Map_To', (string) $container->ID)->count());
    }

    /** @test */
    public function it_replaces_single_photos_and_appends_flexibag_photos_on_edit(): void
    {
        $vs = $this->makeVesselSchedule();
        $this->createContainerWithFlexibag($vs, 'TESTU-PHOTO-4', 1, [
            'Loading_Start_Photo' => $this->image('loading-start-old.jpg'),
        ]);

        $container = DB::table('vessel_schedule_dtls')->where('Container', 'TESTU-PHOTO-4')->first();
        $this->rememberContainerFiles($container->ID);

        $oldSingle = DB::table('meta_files')->where('Topic', 'stuffing-loading-start-photo')->where('Map_To', (string) $container->ID)->first();
        $oldPath = public_path('filevendor/' . $oldSingle->Path);

        $response = $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-save?task=' . $vs->id, [
                'id' => $container->ID,
                'Date' => '2026-11-02',
                'Container' => 'TESTU-PHOTO-4',
                'Tare' => 2000,
                'Netto_KG' => 18000,
                'Flexibag_Photos' => [$this->image('flex-append.jpg')],
                'Loading_Start_Photo' => $this->image('loading-start-new.jpg'),
            ]);

        $response->assertOk();
        $this->rememberContainerFiles($container->ID);

        $this->assertSame(1, DB::table('meta_files')->where('Topic', 'stuffing-loading-start-photo')->where('Map_To', (string) $container->ID)->count());
        $this->assertFalse(is_file($oldPath));
        $this->assertSame(2, DB::table('meta_files')->where('Topic', 'stuffing-flexibag-photo')->where('Map_To', (string) $container->ID)->count());
    }

    /** @test */
    public function it_deletes_an_existing_container_photo(): void
    {
        $vs = $this->makeVesselSchedule();
        $this->createContainerWithFlexibag($vs, 'TESTU-PHOTO-5', 2);

        $container = DB::table('vessel_schedule_dtls')->where('Container', 'TESTU-PHOTO-5')->first();
        $this->rememberContainerFiles($container->ID);
        $photo = DB::table('meta_files')->where('Topic', 'stuffing-flexibag-photo')->where('Map_To', (string) $container->ID)->first();
        $path = public_path('filevendor/' . $photo->Path);

        $response = $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-photo-delete?task=' . $vs->id, [
                'container_id' => $container->ID,
                'photo_id' => $photo->ID,
            ]);

        $response->assertOk();
        $this->assertNull(DB::table('meta_files')->where('ID', $photo->ID)->first());
        $this->assertFalse(is_file($path));
        $this->assertSame(1, DB::table('meta_files')->where('Topic', 'stuffing-flexibag-photo')->where('Map_To', (string) $container->ID)->count());
    }

    /** @test */
    public function it_saves_the_shipping_checklist_and_renders_it_on_the_schedule(): void
    {
        $vs = $this->makeVesselSchedule();

        $response = $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-checklist?task=' . $vs->id, [
                'Checklist_Pickup_Container' => 1,
                'Checklist_Stuffing_Process' => 1,
                'Checklist_Rilis_NPE' => 1,
                'Checklist_Get_In_CY' => 1,
                'Checklist_Done_Kapal_Berangkat' => 1,
            ]);

        $response->assertOk()->assertJson(['status' => 'success']);

        $schedule = DB::table('vessel_schedules')->where('id', $vs->id)->first();
        $this->assertSame(1, (int) $schedule->Checklist_Pickup_Container);
        $this->assertSame(1, (int) $schedule->Checklist_Stuffing_Process);
        $this->assertSame(1, (int) $schedule->Checklist_Rilis_NPE);
        $this->assertSame(1, (int) $schedule->Checklist_Get_In_CY);
        $this->assertSame(1, (int) $schedule->Checklist_Done_Kapal_Berangkat);
        $this->assertSame($this->testUser()->username, $schedule->Checklist_Updated_By);
        $this->assertNotNull($schedule->Checklist_Updated_Date);

        $page = $this->actingAs($this->testUser())
            ->get('/cns-stuffing-detail?task=' . $vs->id);

        $page->assertOk();
        $page->assertSee('CHECKLIST STATUS PENGIRIMAN');
        $page->assertSee('id="Checklist_Pickup_Container"', false);
        $page->assertSee('Simpan Checklist');
    }

    /** @test */
    public function it_returns_existing_photos_in_the_container_edit_data(): void
    {
        $vs = $this->makeVesselSchedule();
        $this->createContainerWithFlexibag($vs, 'TESTU-PHOTO-6', 1, [
            'Seal_Shipping_Photo' => $this->image('seal-shipping.jpg'),
        ]);

        $container = DB::table('vessel_schedule_dtls')->where('Container', 'TESTU-PHOTO-6')->first();
        $this->rememberContainerFiles($container->ID);

        $response = $this->actingAs($this->testUser())
            ->get('/cns-stuffing-detail-data?task=' . $vs->id . '&ID=' . $container->ID);

        $response->assertOk();
        $response->assertJsonStructure([
            'photos' => [
                'flexibag',
                'loading_start',
                'loading_done',
                'seal_customs',
                'seal_shipping',
                'seal_surveyor',
                'road_letter',
                'weighbridge',
            ],
        ]);
        $this->assertCount(1, $response->json('photos.flexibag'));
        $this->assertCount(1, $response->json('photos.seal_shipping'));
    }

    private function createContainerWithFlexibag(VesselSchedule $vs, string $containerName, int $count, array $extra = []): void
    {
        $photos = [];
        for ($i = 1; $i <= $count; $i++) {
            $photos[] = $this->image('flex-existing-' . $i . '.jpg');
        }

        $response = $this->actingAs($this->testUser())
            ->post('/cns-stuffing-detail-save?task=' . $vs->id, array_merge([
                'Date' => '2026-11-02',
                'Container' => $containerName,
                'Tare' => 2000,
                'Netto_KG' => 18000,
                'Flexibag_Photos' => $photos,
            ], $extra));

        $response->assertOk();
        $this->rememberContainerFiles(
            DB::table('vessel_schedule_dtls')->where('Container', $containerName)->value('ID')
        );
    }

    private function singlePhotoPayload(): array
    {
        return [
            'Loading_Start_Photo' => $this->image('loading-start.jpg'),
            'Loading_Done_Photo' => $this->image('loading-done.jpg'),
            'Seal_Customs_Photo' => $this->image('seal-customs.jpg'),
            'Seal_Shipping_Photo' => $this->image('seal-shipping.jpg'),
            'Seal_Surveyor_Photo' => $this->image('seal-surveyor.jpg'),
            'Road_Letter_Photo' => $this->image('road-letter.jpg'),
            'Weighbridge_Photo' => $this->image('weighbridge.jpg'),
        ];
    }

    private function singleTopics(): array
    {
        return [
            'stuffing-loading-start-photo',
            'stuffing-loading-done-photo',
            'stuffing-seal-customs-photo',
            'stuffing-seal-shipping-photo',
            'stuffing-seal-surveyor-photo',
            'stuffing-road-letter-photo',
            'stuffing-container-weighbridge-photo',
        ];
    }

    private function image(string $name): UploadedFile
    {
        return UploadedFile::fake()->image($name, 100, 100);
    }

    private function rememberContainerFiles($containerId): void
    {
        $paths = DB::table('meta_files')->where('Map_To', (string) $containerId)->pluck('Path')->all();

        foreach ($paths as $path) {
            $this->uploadedPaths[] = $path;
        }
    }

    private function makeVesselSchedule(): VesselSchedule
    {
        return VesselSchedule::create([
            'Task_ID' => 'VS/TEST/' . uniqid(),
            'Order_No' => 'TEST-PHOTO-' . uniqid(),
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
}
