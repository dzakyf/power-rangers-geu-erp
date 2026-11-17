<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrainingApplicationSubmitTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('Position', 'Administrator')->firstOrFail();
    }

    private function seedMasterTraining(): string
    {
        $trainingNo = 'TRN/TEST/' . uniqid();

        DB::table('master_training')->insert([
            'training_no' => $trainingNo,
            'target' => 'staff',
            'category' => 'General',
            'training_name' => 'Training Application Test',
            'training_coverage' => 'All',
            'frequency' => 'year',
            'training_month' => 0,
            'status' => 'OPEN',
            'created_by' => 'test',
            'created_date' => now(),
        ]);

        return $trainingNo;
    }

    private function payload(string $trainingNo): array
    {
        return [
            'training_no' => $trainingNo,
            'participant' => 'admin',
            'participant_name' => 'Administrator',
            'participant_position' => 'Administrator',
            'participant_department' => 'ICT',
            'participant_email' => 'admin@example.test',
            'training_why' => 'Need this training',
            'training_how' => 'Supports daily work',
            'requested_date_from' => '2026-12-01',
            'requested_date_to' => '2026-12-02',
            'requested_time_from' => '09:00',
            'requested_time_to' => '12:00',
            'note' => 'Test note',
            'requested_fee' => 150000,
        ];
    }

    /** @test */
    public function valid_submit_creates_a_training_application(): void
    {
        $trainingNo = $this->seedMasterTraining();

        $response = $this->actingAs($this->admin())
            ->post('/partner-training-application-submit', $this->payload($trainingNo));

        $response->assertOk();
        $this->assertSame('success', $response->json('status'));

        $row = DB::table('training_application')->where('training_no', $trainingNo)->first();
        $this->assertNotNull($row, 'The application must be stored');
        $this->assertSame('Training Application Test', $row->training_name);
        $this->assertSame('REQUESTED', $row->status);
        $this->assertSame('admin', $row->participant);
        $this->assertSame('admin', $row->created_by);
    }

    /** @test */
    public function saved_application_appears_in_the_training_index_xml(): void
    {
        $trainingNo = $this->seedMasterTraining();

        $this->actingAs($this->admin())
            ->post('/partner-training-application-submit', $this->payload($trainingNo))
            ->assertOk();

        ob_start();
        $response = $this->actingAs($this->admin())->get('/xml-partner-training');
        $body = (string) ob_get_clean();
        $response->assertOk();

        $this->assertStringContainsString('Training Application Test', $body);
    }

    /** @test */
    public function unknown_training_number_is_rejected_cleanly(): void
    {
        $response = $this->actingAs($this->admin())
            ->post('/partner-training-application-submit', $this->payload('TRN/DOES-NOT-EXIST'));

        $response->assertStatus(422);
        $this->assertSame('error', $response->json('status'));
        $this->assertNull(DB::table('training_application')->where('training_no', 'TRN/DOES-NOT-EXIST')->first());
    }

    /** @test */
    public function application_form_serializes_named_fields_and_redirects_to_the_index(): void
    {
        $response = $this->actingAs($this->admin())->get('/partner-training-application');

        $response->assertOk();
        $response->assertSee("find('input, select, textarea').serialize()", false);
        $response->assertSee("window.location.href = 'partner-training'", false);
    }
}
