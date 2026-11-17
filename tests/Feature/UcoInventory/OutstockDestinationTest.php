<?php

namespace Tests\Feature\UcoInventory;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The warehouse destination chosen while creating an outstock draft must
 * carry through to the detail/processing page and the printed delivery note.
 */
class OutstockDestinationTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('username', 'admin')->firstOrFail();
    }

    private function seedWarehouse(string $taskId): int
    {
        return DB::table('warehouse_partner')->insertGetId([
            'warehouse_task_id'  => $taskId,
            'warehouse_type'     => 'mobil',
            'Location'           => 'Test',
            'warehouse_location' => 'Origin '.$taskId,
            'warehouse_status'   => 'ACTIVE',
            'partner_task_id'    => 'PT-'.uniqid(),
            'Created_Date'       => now(),
            'Created_By'         => '__test_dest__',
        ]);
    }

    private function seedDestination(string $name): int
    {
        return DB::table('master_warehouse')->insertGetId([
            'Name' => $name,
            'Type' => 'SITE',
        ]);
    }

    private function makeOutstock(int $originId, int $destinationId, string $status = 'LOADING'): int
    {
        $id = DB::table('outstock')->insertGetId([
            'Code'                  => 'OST-DEST-'.uniqid(),
            'Date'                  => now()->toDateString(),
            'Warehouse_Origin'      => $originId,
            'Warehouse_Destination' => $destinationId,
            'Status'                => $status,
            'Created_At'            => now(),
        ]);

        DB::table('outstock_warehouses')->insert([
            'Outstock_ID' => $id,
            'Warehouse_ID' => $originId,
        ]);

        return $id;
    }

    /** @test */
    public function detail_page_shows_and_prefills_draft_warehouse_destination()
    {
        $destName = 'Dest Test '.uniqid();
        $origin = $this->seedWarehouse('TEST-DEST-A');
        $destination = $this->seedDestination($destName);
        $doc = $this->makeOutstock($origin, $destination);

        $html = $this->actingAs($this->admin())
            ->get('/partner-outstock-detail?id='.$doc)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Warehouse Tujuan', $html);
        $this->assertStringContainsString($destName, $html);
        $this->assertStringContainsString('name="Destination" value="'.$destName.'"', $html);
    }

    /** @test */
    public function delivery_note_uses_draft_warehouse_destination()
    {
        $destName = 'Dest Note '.uniqid();
        $origin = $this->seedWarehouse('TEST-DEST-B');
        $destination = $this->seedDestination($destName);
        $doc = $this->makeOutstock($origin, $destination);

        $response = $this->actingAs($this->admin())
            ->get('/partner-outstock-delivery-note?id='.$doc.'&warehouse_id='.$origin);
        $response->assertOk();

        $file = $response->baseResponse->getFile()->getPathname();
        $this->assertFileExists($file);

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($file) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($file);

        $this->assertStringContainsString($destName, strip_tags($xml));
    }
}
