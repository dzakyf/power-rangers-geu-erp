<?php

namespace Tests\Feature\CNS;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class MasterCompanyImportTest extends TestCase
{
    use DatabaseTransactions;

    private function testUser(): User
    {
        $user = User::query()->orderBy('id')->first();
        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }

        Gate::define('cns.master_company.view', fn() => true);
        Gate::define('cns.master_company.create', fn() => true);

        return $user;
    }

    private function makeXlsxUpload(array $rows, string $name = 'companies.xlsx'): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1');

        $tmp = tempnam(sys_get_temp_dir(), 'mc_import_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmp);

        return new UploadedFile($tmp, $name, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    /** @test */
    public function it_downloads_master_company_import_template(): void
    {
        $response = $this->actingAs($this->testUser())
            ->get('/cns-master-company-import-template');

        $response->assertOk();
        $this->assertStringContainsString(
            'master-company-import-template.xlsx',
            (string) $response->headers->get('Content-Disposition')
        );
    }

    /** @test */
    public function it_imports_valid_rows_skips_duplicates_and_reports_invalid_rows(): void
    {
        $user = $this->testUser();
        $existingName = 'PT Existing Import ' . uniqid();
        $newName1 = 'PT New Import One ' . uniqid();
        $newName2 = 'PT New Import Two ' . uniqid();

        DB::table('supplier')->insert([
            'Supplier_ID'  => 'GEU-COMP-19999',
            'Company_Name' => $existingName,
            'Is_Company'   => 1,
            'Email'        => 'existing@example.com',
            'Status'       => 'NORMAL',
            'Created_By'   => $user->username,
            'Location'     => $user->Location,
        ]);

        $file = $this->makeXlsxUpload([
            ['Company_Name', 'Is_Consignee', 'Is_Supplier', 'Contact_Person', 'City', 'Email'],
            [$newName1, 'Yes', 'No', 'Andi', 'Jakarta', 'andi@one.com; sales@one.com'],
            [$existingName, 'No', 'Yes', 'Budi', 'Surabaya', 'budi@existing.com'],
            [$newName1, 'Yes', 'No', 'Dup', 'Jakarta', 'dup@one.com'],
            ['', 'No', 'No', 'NoName', 'Bandung', 'noname@example.com'],
            [$newName2, 'No', 'Yes', 'BadEmail', 'Medan', 'not-an-email'],
        ]);

        $response = $this->actingAs($user)
            ->postJson('/cns-master-company-import', ['file' => $file]);

        $response->assertOk()
            ->assertJsonPath('imported', 1)
            ->assertJsonCount(2, 'skipped')
            ->assertJsonCount(2, 'errors');

        $inserted = DB::table('supplier')
            ->where('Location', $user->Location)
            ->where('Company_Name', $newName1)
            ->first();

        $this->assertNotNull($inserted);
        $this->assertSame(1, (int) $inserted->Is_Company);
        $this->assertSame(1, (int) $inserted->Is_Consignee);
        $this->assertSame(0, (int) $inserted->Is_Supplier);
        $this->assertSame('andi@one.com', $inserted->Email);
        $this->assertSame(['andi@one.com', 'sales@one.com'], json_decode($inserted->Emails, true));
    }

    /** @test */
    public function it_rejects_file_missing_required_headers(): void
    {
        $file = $this->makeXlsxUpload([
            ['Wrong_Header', 'Phone'],
            ['PT Test', '08123'],
        ]);

        $response = $this->actingAs($this->testUser())
            ->postJson('/cns-master-company-import', ['file' => $file]);

        $response->assertStatus(422);
    }
}
