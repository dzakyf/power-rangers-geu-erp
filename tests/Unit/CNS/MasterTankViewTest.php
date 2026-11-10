<?php

namespace Tests\Unit\CNS;

use App\MasterTank;
use App\User;
use Tests\TestCase;

class MasterTankViewTest extends TestCase
{
    /** @test */
    public function master_tank_view_renders_grid_and_action_buttons()
    {
        $user = new User([
            'id'       => 1,
            'name'     => 'Test Admin',
            'username' => 'admin',
            'Priv_ID'  => 'ADM',
            'Location' => 'Jakarta',
        ]);
        $this->actingAs($user);

        $html = view('cns.master.tank')->render();

        $this->assertStringContainsString('Master Tangki', $html);
        $this->assertStringContainsString('MasterTank', $html);
        $this->assertStringContainsString('NewTankBtn', $html);
        $this->assertStringContainsString('DetailTankBtn', $html);
        $this->assertStringContainsString('MasterTankDet', $html);
        $this->assertStringContainsString('xml-cns-master-tank', $html);

        // Ensure no popups / modals are used
        $this->assertStringNotContainsString('modalTankForm', $html);
        $this->assertStringNotContainsString('modalTankLogs', $html);
    }

    /** @test */
    public function tank_new_view_renders_form_fields_without_popup()
    {
        $html = view('cns.master.tank-new')->render();

        $this->assertStringContainsString('NEW TANGKI', $html);
        $this->assertStringContainsString('id="new_tank_name"', $html);
        $this->assertStringContainsString('id="new_tank_capacity"', $html);
        $this->assertStringContainsString('id="new_tank_status"', $html);
        $this->assertStringContainsString('submitNewTank', $html);
        $this->assertStringContainsString('cancelTankForm', $html);
    }

    /** @test */
    public function tank_detail_view_renders_tabs_and_audit_logs_without_popup()
    {
        $tank = new MasterTank([
            'name'       => 'Tangki-Test',
            'capacity'   => 500000,
            'status'     => 'ACTIVE',
            'notes'      => 'Tangki uji coba',
            'created_by' => 'Tester',
        ]);
        $tank->id = 999;
        $tank->setRelation('logs', collect());

        $html = view('cns.master.tank-detail', compact('tank'))->render();

        $this->assertStringContainsString('DETAIL TANGKI: Tangki-Test', $html);
        $this->assertStringContainsString('id="tab-tank-data"', $html);
        $this->assertStringContainsString('id="tab-tank-logs"', $html);
        $this->assertStringContainsString('id="edit_tank_name"', $html);
        $this->assertStringContainsString('value="Tangki-Test"', $html);
        $this->assertStringContainsString('submitUpdateTank', $html);
        $this->assertStringContainsString('deleteDetailTank', $html);
        $this->assertStringContainsString('cancelTankForm', $html);
    }

    /** @test */
    public function inventory_index_view_renders_tank_monitoring_card_section()
    {
        $content = file_get_contents(resource_path('views/cns/inventory/index.blade.php'));

        $this->assertStringContainsString('tankMonitoringCardSection', $content);
        $this->assertStringContainsString('tankCardsContainer', $content);
        $this->assertStringContainsString('loadTankMonitoringCards', $content);
        $this->assertStringContainsString('filterByTank', $content);
    }

    /** @test */
    public function inventory_create_view_renders_tank_select_dropdown()
    {
        $html = view('cns.inventory.create')->render();

        $this->assertStringContainsString('<select name="Nama_Tangki" id="Nama_Tangki"', $html);
        $this->assertStringContainsString('loadCreateTankDropdown', $html);
    }

    /** @test */
    public function inventory_edit_view_renders_tank_select_dropdown()
    {
        $html = view('cns.inventory.edit')->render();

        $this->assertStringContainsString('<select name="Nama_Tangki" id="edit_Nama_Tangki"', $html);
        $this->assertStringContainsString('populateEditTankDropdown', $html);
    }
}
