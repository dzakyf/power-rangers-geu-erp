<?php

namespace Tests\Feature\BusinessPartner;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BrandingMarketingDisplayTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('Position', 'Administrator')->firstOrFail();
    }

    private function seedMarketing(array $overrides = []): int
    {
        return DB::table('branding_marketing')->insertGetId(array_merge([
            'Code' => 'MKT/TEST/' . uniqid(),
            'Name' => 'Material Alpha',
            'Type' => 'POSTER',
            'Platform' => 'ONLINE',
            'Channel' => 'INSTAGRAM',
            'Status' => 'ACTIVE',
            'Media_Url' => 'https://example.test/material',
            'Created_At' => now(),
        ], $overrides));
    }

    private function seedPromotion($marketingId, array $overrides = []): int
    {
        return DB::table('branding_promotion')->insertGetId(array_merge([
            'Code' => 'PROM/TEST/' . uniqid(),
            'Department' => 'Marketing',
            'Type' => 'ONLINE',
            'Platform' => 'GOOGLE_ADS',
            'Marketing_Id' => (string) $marketingId,
            'Description' => 'Promotion test',
            'Audience' => 'Public',
            'Estimation' => 100000,
            'Status' => 'SUBMIT',
            'Created_At' => now(),
        ], $overrides));
    }

    private function seedMonitoring($promotionId, array $overrides = []): int
    {
        return DB::table('branding_monitoring')->insertGetId(array_merge([
            'Promotion_Id' => (string) $promotionId,
            'Campaign_Start_At' => now()->subDays(5),
            'Campaign_End_At' => now(),
            'Platform' => 'GOOGLE_ADS',
            'Budget_Realization' => 500000,
            'Campaign_Status' => 'RUNNING',
            'PIC_Id' => (string) $this->admin()->id,
        ], $overrides));
    }

    private function findRow(\SimpleXMLElement $xml, string $id): ?\SimpleXMLElement
    {
        foreach ($xml->row as $row) {
            if ((string) $row['ID'] === $id) {
                return $row;
            }
        }

        return null;
    }

    private function findRowByAttribute(\SimpleXMLElement $xml, string $attribute, string $value): ?\SimpleXMLElement
    {
        foreach ($xml->row as $row) {
            if ((string) $row[$attribute] === $value) {
                return $row;
            }
        }

        return null;
    }

    /** @test */
    public function branding_create_pages_do_not_load_duplicate_jquery(): void
    {
        $pages = [
            '/partner-branding-promotion-create',
            '/partner-branding-monitoring-create',
            '/partner-branding-marketing-create',
            '/partner-branding-evaluation-index',
        ];

        foreach ($pages as $page) {
            $response = $this->actingAs($this->admin())->get($page);
            $response->assertOk();
            $response->assertDontSee('code.jquery.com/jquery-3.6.0.min.js', false);
        }
    }

    /** @test */
    public function promotion_create_maps_select2_text_to_the_material_name(): void
    {
        $response = $this->actingAs($this->admin())->get('/partner-branding-promotion-create');

        $response->assertOk();
        $response->assertSee('cells.eq(3).text()', false);
    }

    /** @test */
    public function promotion_list_displays_the_marketing_material_name(): void
    {
        $marketingId = $this->seedMarketing(['Name' => 'Material Display Name']);
        $promotionId = $this->seedPromotion($marketingId);

        $response = $this->actingAs($this->admin())->get('/partner-branding-promotion-data');
        $response->assertOk();

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);

        $row = $this->findRow($xml, (string) $promotionId);
        $this->assertNotNull($row, 'Promotion row must be present');

        $cells = $row->cell;
        $this->assertSame('Material Display Name', (string) $cells[6], 'Materi Marketing must show the material name');
    }

    /** @test */
    public function promotion_list_keeps_rows_when_the_marketing_material_is_missing(): void
    {
        $promotionId = $this->seedPromotion('999999');

        $response = $this->actingAs($this->admin())->get('/partner-branding-promotion-data');
        $response->assertOk();

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);

        $row = $this->findRow($xml, (string) $promotionId);
        $this->assertNotNull($row, 'Promotion with missing marketing material must stay visible');

        $cells = $row->cell;
        $this->assertSame('-', (string) $cells[6]);
    }

    /** @test */
    public function monitoring_list_displays_the_marketing_material_name(): void
    {
        $marketingId = $this->seedMarketing(['Name' => 'Monitoring Material Name']);
        $promotionId = $this->seedPromotion($marketingId);
        $monitoringId = $this->seedMonitoring($promotionId);

        $response = $this->actingAs($this->admin())->get('/partner-branding-monitoring-data');
        $response->assertOk();

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);

        $row = $this->findRowByAttribute($xml, 'Monitoring_Id', (string) $monitoringId);
        $this->assertNotNull($row, 'Monitoring row must be present');

        $cells = $row->cell;
        $this->assertSame('Monitoring Material Name', (string) $cells[5], 'Materi Marketing must show the material name');
    }

    /** @test */
    public function monitoring_list_keeps_rows_when_the_marketing_material_is_missing(): void
    {
        $promotionId = $this->seedPromotion('999999');
        $monitoringId = $this->seedMonitoring($promotionId);

        $response = $this->actingAs($this->admin())->get('/partner-branding-monitoring-data');
        $response->assertOk();

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);

        $row = $this->findRowByAttribute($xml, 'Monitoring_Id', (string) $monitoringId);
        $this->assertNotNull($row, 'Monitoring with missing marketing material must stay visible');

        $cells = $row->cell;
        $this->assertSame('-', (string) $cells[5]);
    }

    /** @test */
    public function marketing_data_source_exposes_the_material_name_in_cell_three(): void
    {
        $marketingId = $this->seedMarketing(['Name' => 'Material Source Name']);

        $response = $this->actingAs($this->admin())->get('/partner-branding-marketing-data');
        $response->assertOk();

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);

        $row = $this->findRow($xml, (string) $marketingId);
        $this->assertNotNull($row, 'Marketing row must be present');

        $cells = $row->cell;
        $this->assertSame('Material Source Name', (string) $cells[3], 'Select2 name must be sourced from cell 3');
    }
}
