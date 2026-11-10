<?php

namespace Tests\Unit\CNS;

use App\MasterTank;
use Tests\TestCase;

class MasterTankTest extends TestCase
{
    /** @test */
    public function master_tank_model_has_correct_fillable_and_table()
    {
        $tank = new MasterTank([
            'name'       => 'Tangki-A',
            'capacity'   => 50000.50,
            'notes'      => 'Tangki Utama UCO',
            'status'     => 'ACTIVE',
            'created_by' => 'Tester',
        ]);

        $this->assertEquals('cns_master_tanks', $tank->getTable());
        $this->assertEquals('Tangki-A', $tank->name);
        $this->assertEquals(50000.50, $tank->capacity);
        $this->assertEquals('Tangki Utama UCO', $tank->notes);
        $this->assertEquals('ACTIVE', $tank->status);
        $this->assertEquals('Tester', $tank->created_by);
    }
}
