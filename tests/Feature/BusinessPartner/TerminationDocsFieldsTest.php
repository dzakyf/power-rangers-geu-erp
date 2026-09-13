<?php

namespace Tests\Feature\BusinessPartner;

use App\Http\Controllers\RenewalController;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Termination request letter: the Jenis Terminasi select must mark the
 * matching checkbox in the document, and the Alasan Terminasi textarea must
 * be rendered into the letter and persisted on partner_termination.
 */
class TerminationDocsFieldsTest extends TestCase
{
    use DatabaseTransactions;

    private const TEMPLATE = 'template-surat-terminasi-kemitraan.docx';

    private function testUser(): User
    {
        $user = User::query()->orderBy('id')->first();
        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }
        return $user;
    }

    private function seedPartner(string $leadId): int
    {
        return DB::table('opportunity_partner')->insertGetId([
            'full_name' => 'Termination Fields Partner',
            'register_as' => 'individual',
            'status' => 'ACTIVE',
            'lead_id' => $leadId,
            'contract_end_at' => '2026-12-31 00:00:00',
            'user_row_id' => $this->testUser()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function documentText(string $path): string
    {
        // Read word/document.xml out of the .docx zip, then strip XML tags so
        // the checkbox glyphs / reasons can be asserted.
        $zip = new \ZipArchive();
        $zip->open($path);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        return preg_replace('/<[^>]*>/', '', $xml);
    }

    /** @test */
    public function template_exposes_the_termination_type_and_reason_macros(): void
    {
        $zip = new \ZipArchive();
        $abs = public_path(self::TEMPLATE);
        $this->assertTrue($zip->open($abs) === true, 'Termination template must exist');
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        $text = preg_replace('/<[^>]*>/', '', $xml);
        foreach (['${type1}', '${type2}', '${type3}', '${type4}', '${alasan}'] as $macro) {
            $this->assertStringContainsString($macro, $text, "Missing macro {$macro}");
        }
    }

    /** @test */
    public function selected_type_marks_exactly_its_checkbox_and_the_reason_is_rendered(): void
    {
        $opportunityId = $this->seedPartner('BP/TEST-TRM-FIELDS/0001');

        $result = app(RenewalController::class)->makeTerminationDocs([
            'opportunity_id' => $opportunityId,
            'type' => 'KESEPAKATAN_BERSAMA',
            'reason' => 'Alasan terminasi uji otomatis',
        ]);
        $this->assertIsArray($result);

        $tmp = tempnam(sys_get_temp_dir(), 'trm').'.docx';
        $result['doc']->saveAs($tmp);

        $text = $this->documentText($tmp);
        @unlink($tmp);

        $this->assertSame(1, substr_count($text, '☒'), 'Exactly one termination type must be marked');
        $this->assertStringContainsString('Alasan terminasi uji otomatis', $text);

        // Only the selected option may be marked.
        $this->assertStringContainsString('☒ Kesepakatan Bersama', $text);
        $this->assertStringNotContainsString('☒ Berakhir Masa Kontrak', $text);
        $this->assertStringNotContainsString('☒ Pengakhiran oleh Mitra', $text);
        $this->assertStringNotContainsString('☒ Pengakhiran oleh Perusahaan', $text);
    }

    /** @test */
    public function finalize_persists_type_and_reason(): void
    {
        $opportunityId = $this->seedPartner('BP/TEST-TRM-FIELDS/0002');

        $response = $this->actingAs($this->testUser())->post('/partner-termination-finalize-application', [
            'opportunity_id' => $opportunityId,
            'type' => 'PENGAKHIRAN_OLEH_MITRA',
            'reason' => 'Mitra mengajukan pengakhiran',
        ]);

        $response->assertOk();

        $row = DB::table('partner_termination')->where('Opportunity_Id', $opportunityId)->orderByDesc('ID')->first();
        $this->assertNotNull($row);
        $this->assertSame('PENGAKHIRAN_OLEH_MITRA', $row->Type);
        $this->assertSame('Mitra mengajukan pengakhiran', $row->Resolution);
    }
}
