<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression tests for the Training module fixes:
 *  - Materials: uploaded attachments must resolve to a real public URL.
 *  - Master Questions: "Save Training" must persist (no undefined _c3/_c4).
 *  - Master Training: schedule cell is blank for monthly, month-year otherwise.
 *  - View configs: null-safe row selection + editable dropdown columns.
 */
class TrainingFixesTest extends TestCase
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
    public function library_material_attachment_resolves_to_a_public_file_url(): void
    {
        DB::table('master_materials')->insert([
            'material_no' => 'MTR/TEST/0001',
            'material_name' => 'Training Fix Material',
            'material_category' => 'Umum',
            'material_status' => 'OPEN',
            'material_download' => 1,
            'material_view' => 1,
            'material_attachment' => '2026/09/fileinvendor-test.pdf',
            'created_by' => $this->testUser()->username,
            'created_date' => now(),
        ]);

        ob_start();
        $response = $this->actingAs($this->testUser())->get('/xml-partner-training-library');
        $body = (string) ob_get_clean();

        $response->assertOk();
        $this->assertStringContainsString(
            asset('filevendor/2026/09/fileinvendor-test.pdf'),
            $body,
            'Uploaded material attachments must be exposed under filevendor/'
        );
    }

    /** @test */
    public function library_view_and_download_cells_are_links(): void
    {
        DB::table('master_materials')->insert([
            'material_no' => 'MTR/TEST/0002',
            'material_name' => 'Link Columns Material',
            'material_category' => 'Umum',
            'material_status' => 'OPEN',
            'material_download' => 1,
            'material_view' => 1,
            'material_attachment' => '2026/09/fileinvendor-test.pdf',
            'created_by' => $this->testUser()->username,
            'created_date' => now(),
        ]);

        ob_start();
        $response = $this->actingAs($this->testUser())->get('/xml-partner-training-library');
        $body = (string) ob_get_clean();

        $response->assertOk();
        $url = asset('filevendor/2026/09/fileinvendor-test.pdf');

        // dhtmlx "link" cells receive "label^url" and render a hyperlink.
        $this->assertStringContainsString('View^'.$url, $body);
        $this->assertStringContainsString('Download^'.$url, $body);
    }

    /** @test */
    public function library_grid_declares_link_columns_and_drops_the_row_select_handler(): void
    {
        $response = $this->actingAs($this->testUser())->get('/partner-training-library');

        $response->assertOk();
        $body = (string) $response->getContent();

        $this->assertStringContainsString('setColTypes("ron,ro,ro,ro,link,link")', $body);
        $this->assertStringNotContainsString("split('||')", $body);
        $this->assertStringNotContainsString('onRowSelect', $body);
    }

    /** @test */
    public function master_question_save_persists_without_undefined_cell_reads(): void
    {
        $body = 'ids=u1&u1_c1=mitra&u1_c2=Test%20Question&u1_!nativeeditor_status=inserted';

        $response = $this->actingAs($this->testUser())->call(
            'POST',
            '/partner-master-satisfication-update',
            [],
            [],
            [],
            [],
            $body
        );

        $response->assertOk();

        $row = DB::table('master_satisfication')
            ->where('target', 'mitra')
            ->where('question', 'Test Question')
            ->first();

        $this->assertNotNull($row, 'The master question must be saved');
    }

    private function seedTraining(string $frequency, int $month): int
    {
        return DB::table('master_training')->insertGetId([
            'training_no' => 'TRN/TEST/'.uniqid(),
            'target' => 'mitra',
            'category' => 'Test',
            'training_name' => 'Training '.$frequency,
            'training_coverage' => 'Test',
            'frequency' => $frequency,
            'training_month' => $month,
            'status' => 'OPEN',
            'created_by' => $this->testUser()->username,
            'created_date' => now(),
        ]);
    }

    /** @test */
    public function master_training_schedule_is_blank_for_monthly_and_month_year_for_yearly(): void
    {
        $yearlyId = $this->seedTraining('year', 9);
        $monthlyId = $this->seedTraining('month', 9);

        ob_start();
        $response = $this->actingAs($this->testUser())->get('/xml-partner-master-training');
        $body = (string) ob_get_clean();

        $response->assertOk();

        // Yearly row: schedule cell shows "September <year>".
        $expected = date('F Y', strtotime(date('Y').'-09-01'));
        $this->assertStringContainsString($expected, $body, 'Yearly training must show its scheduled month');

        // Monthly row: schedule cell is empty (not "continue").
        $monthly = DB::table('master_training')->where('id', $monthlyId)->first();
        $this->assertNotNull($monthly);
        $this->assertStringNotContainsString('continue', $body, 'Monthly frequency must not render a "continue" schedule');
    }

    /** @test */
    public function attendance_button_uses_a_null_safe_selection_check(): void
    {
        $response = $this->actingAs($this->testUser())->get('/partner-training-attend');

        $response->assertOk();
        $body = (string) $response->getContent();
        $this->assertStringContainsString('TrainingAttendID != null', $body);
        $this->assertStringNotContainsString('TrainingAttendID.length != null', $body);
    }

    /** @test */
    public function master_question_grid_offers_a_peruntukan_dropdown_and_blank_new_rows(): void
    {
        $response = $this->actingAs($this->testUser())->get('/partner-master-satisfication');

        $response->assertOk();
        $body = (string) $response->getContent();

        $this->assertStringContainsString('setColTypes("ron,combo,ed")', $body);
        $this->assertStringContainsString('getColumnCombo(1)', $body);
        // New rows must not pre-fill the allocation column with today's date
        // nor the question column with '0'.
        $this->assertStringContainsString("addRow(uid, [num, '', '', '', ''])", $body);
        $this->assertStringNotContainsString('addRow(uid, [num,"'.now()->format('Y-m-d'), $body);
    }

    /** @test */
    public function master_training_grid_exposes_editable_dropdown_columns(): void
    {
        $response = $this->actingAs($this->testUser())->get('/partner-master-training');

        $response->assertOk();
        $body = (string) $response->getContent();
        $this->assertStringContainsString('setColTypes("ron,combo,ed,combo,combo,ro")', $body);
        $this->assertStringContainsString('getColumnCombo(1)', $body);
        $this->assertStringContainsString('getColumnCombo(3)', $body);
        $this->assertStringContainsString('getColumnCombo(4)', $body);
    }
}
