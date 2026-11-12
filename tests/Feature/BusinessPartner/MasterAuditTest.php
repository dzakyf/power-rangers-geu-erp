<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MasterAuditTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('Position', 'Administrator')->firstOrFail();
    }

    private function seedCategory(array $overrides = []): int
    {
        return DB::table('master_audit_category')->insertGetId(array_merge([
            'category' => 'TEST-AUDIT-' . uniqid(),
            'category_description' => 'Test category',
            'parameter_list' => 'P1,P2',
            'parameter_score' => '10,20',
            'status' => 'NORMAL',
            'urutan' => 999,
            'created_by' => 'test',
            'created_date' => now(),
        ], $overrides));
    }

    /** @test */
    public function changing_category_status_toggles_between_normal_and_stop(): void
    {
        $id = $this->seedCategory();

        $first = $this->actingAs($this->admin())
            ->post('/partner-master-audit-category-change-status', ['param' => $id]);

        $first->assertOk();
        $this->assertSame('success', $first->json('status'));
        $this->assertSame('STOP', DB::table('master_audit_category')->where('id', $id)->value('status'));

        $second = $this->actingAs($this->admin())
            ->post('/partner-master-audit-category-change-status', ['param' => $id]);

        $second->assertOk();
        $this->assertSame('success', $second->json('status'));
        $this->assertSame('NORMAL', DB::table('master_audit_category')->where('id', $id)->value('status'));
    }

    /** @test */
    public function category_detail_renders_sequential_parameter_rows_and_next_counter(): void
    {
        $id = $this->seedCategory();

        $response = $this->actingAs($this->admin())
            ->get('/partner-master-audit-category-detail?ID=' . $id);

        $response->assertOk();
        $response->assertSee('id="divParameter2"', false);
        $response->assertSee('data-id="2"', false);
        $response->assertSee('count = 3;', false);
    }

    /** @test */
    public function category_update_endpoint_updates_a_row(): void
    {
        $id = $this->seedCategory([
            'category' => 'Old Category',
            'category_description' => 'Old Description',
            'parameter_list' => 'P1',
            'parameter_score' => '10',
        ]);

        $response = $this->actingAs($this->admin())
            ->post('/partner-master-audit-category-update', [
                'ids' => (string) $id,
                $id . '_c1' => 'New Category',
                $id . '_c2' => 'New Description',
                $id . '_c3' => 'A,B',
                $id . '_c4' => '1,2',
                $id . '_c5' => 'STOP',
                $id . '_!nativeeditor_status' => 'updated',
            ]);

        $response->assertOk();
        $this->assertStringContainsString("type='update'", $response->getContent());

        $row = DB::table('master_audit_category')->where('id', $id)->first();
        $this->assertSame('New Category', $row->category);
        $this->assertSame('New Description', $row->category_description);
        $this->assertSame('A,B', $row->parameter_list);
        $this->assertSame('1,2', $row->parameter_score);
        $this->assertSame('STOP', $row->status);
    }

    /** @test */
    public function category_update_endpoint_deletes_a_row(): void
    {
        $id = $this->seedCategory();

        $response = $this->actingAs($this->admin())
            ->post('/partner-master-audit-category-update', [
                'ids' => (string) $id,
                $id . '_!nativeeditor_status' => 'deleted',
            ]);

        $response->assertOk();
        $this->assertStringContainsString("type='delete'", $response->getContent());
        $this->assertNull(DB::table('master_audit_category')->where('id', $id)->first());
    }

    /** @test */
    public function category_update_endpoint_inserts_a_row_and_returns_the_new_id(): void
    {
        $tempId = 'tmp_' . uniqid();

        $response = $this->actingAs($this->admin())
            ->post('/partner-master-audit-category-update', [
                'ids' => $tempId,
                $tempId . '_c1' => 'Inserted Category',
                $tempId . '_c2' => 'Inserted Description',
                $tempId . '_c3' => 'X,Y',
                $tempId . '_c4' => '3,4',
                $tempId . '_c5' => 'NORMAL',
                $tempId . '_!nativeeditor_status' => 'inserted',
            ]);

        $response->assertOk();
        $this->assertStringContainsString("type='insert'", $response->getContent());

        $row = DB::table('master_audit_category')->where('category', 'Inserted Category')->first();
        $this->assertNotNull($row);
        $this->assertSame('Inserted Description', $row->category_description);
        $this->assertSame('NORMAL', $row->status);
        $this->assertStringContainsString("tid='" . $row->id . "'", $response->getContent());
    }
}
