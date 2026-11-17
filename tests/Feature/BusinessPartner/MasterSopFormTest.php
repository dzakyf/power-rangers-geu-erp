<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MasterSopFormTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('Position', 'Administrator')->firstOrFail();
    }

    private function seedCategory(array $overrides = []): int
    {
        return DB::table('master_audit_category')->insertGetId(array_merge([
            'category' => 'SOP TEST ' . uniqid(),
            'category_description' => 'SOP description',
            'parameter_list' => 'P1,P2',
            'parameter_score' => '10,20',
            'status' => 'NORMAL',
            'urutan' => 999,
            'created_by' => 'test',
            'created_date' => now(),
        ], $overrides));
    }

    /** @test */
    public function sop_list_page_is_read_only_and_offers_form_actions(): void
    {
        $response = $this->actingAs($this->admin())->get('/partner-master-sop');

        $response->assertOk();
        $response->assertSee('id="SopGrid"', false);
        $response->assertSee('id="SopFormDiv"', false);
        $response->assertSee('partner-master-sop-new', false);
        $response->assertSee('partner-master-sop-detail', false);
        $response->assertSee('partner-master-sop-delete', false);
        $response->assertDontSee('enableEditEvents', false);
        $response->assertDontSee('PMasterPriceProduct', false);
    }

    /** @test */
    public function sop_new_form_renders_all_fields(): void
    {
        $response = $this->actingAs($this->admin())->get('/partner-master-sop-new');

        $response->assertOk();
        $response->assertSee('name="category"', false);
        $response->assertSee('name="category_description"', false);
        $response->assertSee('name="parameter_list"', false);
        $response->assertSee('name="parameter_score"', false);
        $response->assertSee('name="status"', false);
        $response->assertSee('id="SubmitSop"', false);
    }

    /** @test */
    public function sop_detail_form_renders_existing_values(): void
    {
        $id = $this->seedCategory(['category' => 'SOP EXISTING CATEGORY']);

        $response = $this->actingAs($this->admin())
            ->get('/partner-master-sop-detail?ID=' . $id);

        $response->assertOk();
        $response->assertSee('SOP EXISTING CATEGORY', false);
        $response->assertSee('name="ID"', false);
    }

    /** @test */
    public function sop_new_submit_creates_a_record(): void
    {
        $response = $this->actingAs($this->admin())->post('/partner-master-sop-new-submit', [
            'category' => 'SOP CREATED CATEGORY',
            'category_description' => 'Created by test',
            'parameter_list' => 'A,B',
            'parameter_score' => '1,2',
            'status' => 'NORMAL',
        ]);

        $response->assertOk();
        $this->assertSame('success', $response->json('status'));

        $row = DB::table('master_audit_category')->where('category', 'SOP CREATED CATEGORY')->first();
        $this->assertNotNull($row);
        $this->assertSame('Created by test', $row->category_description);
        $this->assertSame('A,B', $row->parameter_list);
        $this->assertSame('1,2', $row->parameter_score);
        $this->assertSame('NORMAL', $row->status);
    }

    /** @test */
    public function sop_detail_submit_updates_a_record(): void
    {
        $id = $this->seedCategory(['category' => 'SOP BEFORE UPDATE']);

        $response = $this->actingAs($this->admin())->post('/partner-master-sop-detail-submit', [
            'ID' => $id,
            'category' => 'SOP AFTER UPDATE',
            'category_description' => 'Updated by test',
            'parameter_list' => 'X,Y',
            'parameter_score' => '7,8',
            'status' => 'STOP',
        ]);

        $response->assertOk();
        $this->assertSame('success', $response->json('status'));

        $row = DB::table('master_audit_category')->where('id', $id)->first();
        $this->assertSame('SOP AFTER UPDATE', $row->category);
        $this->assertSame('Updated by test', $row->category_description);
        $this->assertSame('X,Y', $row->parameter_list);
        $this->assertSame('7,8', $row->parameter_score);
        $this->assertSame('STOP', $row->status);
    }

    /** @test */
    public function sop_delete_removes_a_record(): void
    {
        $id = $this->seedCategory();

        $response = $this->actingAs($this->admin())
            ->post('/partner-master-sop-delete', ['id' => $id]);

        $response->assertOk();
        $this->assertSame('success', $response->json('status'));
        $this->assertNull(DB::table('master_audit_category')->where('id', $id)->first());
    }

    /** @test */
    public function sop_new_submit_requires_a_category(): void
    {
        $response = $this->actingAs($this->admin())->post('/partner-master-sop-new-submit', [
            'category_description' => 'No category',
        ]);

        $response->assertStatus(422);
    }
}
