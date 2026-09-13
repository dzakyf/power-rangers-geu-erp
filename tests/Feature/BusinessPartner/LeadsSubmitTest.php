<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression tests for the public "New Partner (Leads)" submit
 * (partner/leads.blade.php -> POST sales-opportunity-new-submit).
 *
 * The handler used to reject the submit whenever ANY posted field was empty
 * and only understood the numeric Partnership_Interest values, so a checked
 * source channel (e.g. Instagram) could still be followed by a
 * "tidak boleh kosong" prompt and the legacy booth/mobil values were stored
 * as 0 (which surfaced later as "Opsi 0").
 *
 * Required selections: Full_Name, Phone_Number, Source_Channel,
 * Partnership_Interest, Capital_Estimation.
 */
class LeadsSubmitTest extends TestCase
{
    use DatabaseTransactions;

    private const URL = '/sales-opportunity-new-submit';

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

    private function validFields(array $overrides = []): array
    {
        return array_merge([
            'Full_Name'            => 'Lead Submit Test',
            'Phone_Number'         => '0812'.random_int(1000000, 9999999),
            'Source_Channel'       => 'Instagram',
            'Partnership_Interest' => 'booth',
            'Capital_Estimation'   => '2',
        ], $overrides);
    }

    private function latestOpportunity(string $phone)
    {
        return DB::table('opportunity')->where('Phone_Number', $phone)->orderByDesc('ID')->first();
    }

    /** @test */
    public function booth_string_is_stored_as_booth(): void
    {
        $fields = $this->validFields();

        $this->actingAs($this->testUser())->post(self::URL, $fields)
            ->assertOk()
            ->assertSee('success');

        $row = $this->latestOpportunity($fields['Phone_Number']);
        $this->assertNotNull($row, 'The lead must be created when all required selections are provided');
        $this->assertSame('booth', $row->Hub_Type);
        $this->assertSame(2, (int) $row->Partnership_Interest);
        $this->assertSame('Instagram', $row->Source_Channel);
    }

    /** @test */
    public function mobil_string_is_stored_as_mobil(): void
    {
        $fields = $this->validFields(['Partnership_Interest' => 'mobil']);

        $this->actingAs($this->testUser())->post(self::URL, $fields)->assertSee('success');

        $row = $this->latestOpportunity($fields['Phone_Number']);
        $this->assertSame('mobil', $row->Hub_Type);
        $this->assertSame(1, (int) $row->Partnership_Interest);
    }

    /** @test */
    public function numeric_partnership_interest_is_normalized(): void
    {
        $fields = $this->validFields(['Partnership_Interest' => '1']);

        $this->actingAs($this->testUser())->post(self::URL, $fields)->assertSee('success');

        $row = $this->latestOpportunity($fields['Phone_Number']);
        $this->assertSame('mobil', $row->Hub_Type);
    }

    /** @test */
    public function missing_partnership_interest_reports_that_field(): void
    {
        $fields = $this->validFields(['Partnership_Interest' => '']);

        $this->actingAs($this->testUser())->post(self::URL, $fields)
            ->assertOk()
            ->assertSee('Partnership Interest');

        $this->assertNull($this->latestOpportunity($fields['Phone_Number']));
    }

    /** @test */
    public function source_channel_alone_is_not_enough_without_partnership_interest(): void
    {
        // Documents the required-selection behaviour: checking Instagram is
        // fine, but the partnership interest still has to be chosen.
        $fields = $this->validFields(['Partnership_Interest' => '']);
        unset($fields['Capital_Estimation']);

        $this->actingAs($this->testUser())->post(self::URL, $fields)
            ->assertOk()
            ->assertSee('Partnership Interest');
    }
}
