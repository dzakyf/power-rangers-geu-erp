<?php

namespace Tests\Feature\CNS;

use App\CnsMasterKemasan;
use App\MasterTank;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class MasterDataImportTest extends TestCase
{
    use DatabaseTransactions;

    private function testUser(): User
    {
        $user = User::query()->orderBy('id')->first();
        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }

        foreach (['master_supplier', 'master_consignee', 'master_tank', 'master_kemasan'] as $perm) {
            Gate::define("cns.{$perm}.view", fn() => true);
            Gate::define("cns.{$perm}.create", fn() => true);
        }

        return $user;
    }

    private function makeXlsxUpload(array $rows, string $name = 'import.xlsx'): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1');

        $tmp = tempnam(sys_get_temp_dir(), 'md_import_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmp);

        return new UploadedFile($tmp, $name, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    /** @test */
    public function it_downloads_all_master_data_import_templates(): void
    {
        $user = $this->testUser();

        $cases = [
            '/cns-master-supplier-import-template'  => 'master-supplier-import-template.xlsx',
            '/cns-master-consignee-import-template' => 'master-consignee-import-template.xlsx',
            '/cns-master-tank-import-template'      => 'master-tank-import-template.xlsx',
            '/cns-master-kemasan-import-template'   => 'master-kemasan-import-template.xlsx',
        ];

        foreach ($cases as $url => $expectedFilename) {
            $response = $this->actingAs($user)->get($url);
            $response->assertOk();
            $this->assertStringContainsString(
                $expectedFilename,
                (string) $response->headers->get('Content-Disposition')
            );
        }
    }

    /** @test */
    public function it_imports_master_supplier_and_skips_duplicates(): void
    {
        $user = $this->testUser();
        $dupName = 'PT Dup Supplier ' . uniqid();
        $newName = 'PT New Supplier ' . uniqid();

        DB::table('supplier')->insert([
            'Supplier_ID'  => 'GEU-COMP-19991',
            'Company_Name' => $dupName,
            'Is_Company'   => 1,
            'Is_Supplier'  => 1,
            'Email'        => 'dup@supplier.com',
            'Status'       => 'NORMAL',
            'Created_By'   => $user->username,
            'Location'     => $user->Location,
        ]);

        $file = $this->makeXlsxUpload([
            ['Company_Name', 'Email', 'City'],
            [$newName, 'new@supplier.com', 'Jakarta'],
            [$dupName, 'dup2@supplier.com', 'Bandung'],
            ['', 'empty@supplier.com', 'Surabaya'],
        ]);

        $response = $this->actingAs($user)
            ->postJson('/cns-master-supplier-import', ['file' => $file]);

        $response->assertOk()
            ->assertJsonPath('imported', 1)
            ->assertJsonCount(1, 'skipped')
            ->assertJsonCount(1, 'errors');

        $row = DB::table('supplier')
            ->where('Location', $user->Location)
            ->where('Company_Name', $newName)
            ->first();

        $this->assertNotNull($row);
        $this->assertSame(1, (int) $row->Is_Supplier);
        $this->assertSame(0, (int) $row->Is_Consignee);
    }

    /** @test */
    public function it_imports_master_consignee_and_skips_duplicates(): void
    {
        $user = $this->testUser();
        $newName = 'PT New Consignee ' . uniqid();

        $file = $this->makeXlsxUpload([
            ['Company_Name', 'Email'],
            [$newName, 'buyer@consignee.com'],
            [$newName, 'buyer2@consignee.com'],
        ]);

        $response = $this->actingAs($user)
            ->postJson('/cns-master-consignee-import', ['file' => $file]);

        $response->assertOk()
            ->assertJsonPath('imported', 1)
            ->assertJsonCount(1, 'skipped')
            ->assertJsonCount(0, 'errors');

        $row = DB::table('supplier')
            ->where('Location', $user->Location)
            ->where('Company_Name', $newName)
            ->first();

        $this->assertNotNull($row);
        $this->assertSame(1, (int) $row->Is_Consignee);
        $this->assertSame(0, (int) $row->Is_Supplier);
    }

    /** @test */
    public function it_imports_master_tank_and_validates_rows(): void
    {
        $user = $this->testUser();
        $tankName = 'Tank Import ' . uniqid();

        $file = $this->makeXlsxUpload([
            ['name', 'capacity', 'status', 'notes'],
            [$tankName, 25000, 'ACTIVE', 'Tangki baru'],
            [$tankName, 30000, 'ACTIVE', 'Duplikat'],
            ['Bad Cap Tank ' . uniqid(), -5, 'ACTIVE', ''],
        ]);

        $response = $this->actingAs($user)
            ->postJson('/cns-master-tank-import', ['file' => $file]);

        $response->assertOk()
            ->assertJsonPath('imported', 1)
            ->assertJsonCount(1, 'skipped')
            ->assertJsonCount(1, 'errors');

        $this->assertNotNull(MasterTank::where('name', $tankName)->first());
    }

    /** @test */
    public function it_imports_master_kemasan_and_validates_rows(): void
    {
        $user = $this->testUser();
        $kemasanName = 'Drum Import ' . uniqid();

        $file = $this->makeXlsxUpload([
            ['Name', 'product_type', 'Volume', 'Is_Ampas', 'Max_Stock', 'Unit', 'Price'],
            [$kemasanName, 'Kemasan', 200, 'No', 50, 'Drum', 150000],
            [$kemasanName, 'Kemasan', 200, 'No', 50, 'Drum', 150000],
            ['Invalid Kemasan ' . uniqid(), 'Kemasan', '', 'No', 10, 'Pcs', 1000],
        ]);

        $response = $this->actingAs($user)
            ->postJson('/cns-master-kemasan-import', ['file' => $file]);

        $response->assertOk()
            ->assertJsonPath('imported', 2)
            ->assertJsonCount(0, 'skipped')
            ->assertJsonCount(1, 'errors');

        $this->assertNotNull(CnsMasterKemasan::where('Name', $kemasanName)->first());
    }
}
