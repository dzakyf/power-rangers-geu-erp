<?php

namespace Tests\Unit\CNS;

use Tests\TestCase;

class InventoryPelindoQCTest extends TestCase
{
    /** @test */
    public function create_view_renders_8_qc_parameters_and_average()
    {
        $html = view('cns.inventory.create')->render();

        $this->assertStringContainsString('FFA (As Palmitic)', $html);
        $this->assertStringContainsString('FFA (As Oleic)', $html);
        $this->assertStringContainsString('MIU', $html);
        $this->assertStringContainsString('Total Sulfur', $html);
        $this->assertStringContainsString('Iodine Value', $html);
        $this->assertStringContainsString('Chlorides', $html);
        $this->assertStringContainsString('Phosphorus', $html);
        $this->assertStringContainsString('Total Metals', $html);

        $this->assertStringContainsString('name="top_FFA_As_Palmitic"', $html);
        $this->assertStringContainsString('name="middle_FFA_As_Palmitic"', $html);
        $this->assertStringContainsString('name="bottom_FFA_As_Palmitic"', $html);
        $this->assertStringContainsString('id="create_avg_FFA_As_Palmitic"', $html);
    }

    /** @test */
    public function edit_view_renders_8_qc_parameters_and_average()
    {
        $html = view('cns.inventory.edit')->render();

        $this->assertStringContainsString('FFA (As Palmitic)', $html);
        $this->assertStringContainsString('FFA (As Oleic)', $html);
        $this->assertStringContainsString('MIU', $html);
        $this->assertStringContainsString('Total Sulfur', $html);
        $this->assertStringContainsString('Iodine Value', $html);
        $this->assertStringContainsString('Chlorides', $html);
        $this->assertStringContainsString('Phosphorus', $html);
        $this->assertStringContainsString('Total Metals', $html);

        $this->assertStringContainsString('name="top_FFA_As_Palmitic"', $html);
        $this->assertStringContainsString('id="edit_avg_FFA_As_Palmitic"', $html);
    }

    /** @test */
    public function index_view_defines_qc_parameters_and_link()
    {
        $content = file_get_contents(resource_path('views/cns/inventory/index.blade.php'));

        $this->assertStringContainsString('Parameter QC', $content);
        $this->assertStringContainsString('FFA Palmitic', $content);
        $this->assertStringContainsString('FFA Oleic', $content);
        $this->assertStringContainsString('MIU', $content);
        $this->assertStringContainsString('Sulfur', $content);
        $this->assertStringContainsString('IV', $content);
        $this->assertStringContainsString('Chlorides', $content);
        $this->assertStringContainsString('Phosphorus', $content);
        $this->assertStringContainsString('Metals', $content);
    }
}
