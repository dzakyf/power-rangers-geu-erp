<?php

namespace Tests\Unit\CNS;

use App\Http\Controllers\CNS\VesselScheduleController;
use Tests\TestCase;

class VesselScheduleExclusionTest extends TestCase
{
    /** @test */
    public function vessel_schedule_form_view_locks_fields_in_edit_mode()
    {
        $viewPath = resource_path('views/cns/vs/partials/vessel-schedule-form.blade.php');
        $this->assertFileExists($viewPath);

        $content = file_get_contents($viewPath);

        // Checks for edit mode locking and hidden fallback inputs
        $this->assertStringContainsString('name="Contract_ID" value="{{ $vs->Contract_ID }}"', $content);
        $this->assertStringContainsString('{{ $isEdit ? \'disabled\' : \'\' }}', $content);
        $this->assertStringContainsString('name="Shipping_Method" value="{{ $vs->Shipping_Method }}"', $content);
        $this->assertStringContainsString('name="Container_Booking_ID" value="{{ $vs->Booking_ID }}"', $content);
    }

    /** @test */
    public function vessel_schedule_script_view_guards_change_events_in_edit_mode()
    {
        $scriptPath = resource_path('views/cns/vs/partials/vessel-schedule-form-script.blade.php');
        $this->assertFileExists($scriptPath);

        $content = file_get_contents($scriptPath);

        $this->assertStringContainsString('if (!IS_EDIT)', $content);
        $this->assertStringContainsString('var selectedContract = $(\'#Contract_ID\').val() || $(\'input[name="Contract_ID"]\').val();', $content);
        $this->assertStringContainsString('var selectedMethod = $(\'#Shipping_Method\').val() || $(\'input[name="Shipping_Method"]\').val();', $content);
    }

    /** @test */
    public function controller_has_availability_filtering_methods()
    {
        $ref = new \ReflectionClass(VesselScheduleController::class);

        $this->assertTrue($ref->hasMethod('availableContractsForNew'));
        $this->assertTrue($ref->hasMethod('availableBookingsFor'));

        $contractsMethod = $ref->getMethod('availableContractsForNew');
        $contractsMethod->setAccessible(true);

        $bookingsMethod = $ref->getMethod('availableBookingsFor');
        $bookingsMethod->setAccessible(true);
        $this->assertSame(2, $bookingsMethod->getNumberOfParameters());
    }
}
