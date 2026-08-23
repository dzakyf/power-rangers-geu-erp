<?php

namespace Tests\Feature\BusinessPartner;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Concerns\SeedsAftersalesFixtures;
use Tests\TestCase;

/**
 * Integration tests for the Business Partner module, Dashboard submodule
 * (Aftersales).
 *
 * Routes under test (routes/web.php:5302-5303):
 *   GET partner-dashboard-aftersales      -> SalesController@aftersalesDashboardPage
 *   GET partner-dashboard-aftersales-data -> SalesController@aftersalesDashboardData
 *
 * Both routes sit behind the "web" + "log.access" middleware; guests are
 * redirected to the login page.
 *
 * Runs against the dev database inside a per-test transaction that is rolled
 * back (DatabaseTransactions). See KNOWN-ISSUES.md in this directory for
 * behaviours that are documented rather than fixed.
 */
class AftersalesDashboardTest extends TestCase
{
    use DatabaseTransactions;
    use SeedsAftersalesFixtures;

    private const PAGE_URL = '/partner-dashboard-aftersales';
    private const DATA_URL = '/partner-dashboard-aftersales-data';

    /**
     * All top-level keys of the data endpoint payload.
     */
    private const PAYLOAD_KEYS = [
        'hub_stats',
        'performance',
        'support_request_count',
        'contract_monitoring',
        'support_performance',
        'net_profit',
        'complaints_list',
        'total_income',
        'total_net_profit',
        'uco_volume',
        'uco_volume_sold',
        'management_fee',
        'company_royalty',
        'mitra_royalty',
        'company_total_profit',
    ];

    /**
     * Fixture user, created lazily on first use.
     *
     * IMPORTANT: do not introduce a setUp() override in this class - it would
     * silently replace DatabaseTransactions::setUp() and the per-test
     * rollback would never happen (PHPUnit trait-method resolution).
     *
     * @var \App\User|null
     */
    private $user;

    // ------------------------------------------------------------------
    // 1-2. Page endpoint
    // ------------------------------------------------------------------

    /** @test */
    public function test_guest_is_redirected_to_login_from_page(): void
    {
        $this->get(self::PAGE_URL)
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function test_page_renders_for_authenticated_user(): void
    {
        $this->actingAs($this->testUser())
            ->get(self::PAGE_URL)
            ->assertOk()
            ->assertViewIs('partner.dashboard.aftersales-index')
            ->assertSee('Dashboard Aftersales Management', false);
    }

    // ------------------------------------------------------------------
    // 3-4. Data endpoint: auth + structure
    // ------------------------------------------------------------------

    /** @test */
    public function test_guest_is_redirected_to_login_from_data_endpoint(): void
    {
        $this->get(self::DATA_URL)
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function test_data_endpoint_returns_expected_structure(): void
    {
        $json = $this->fetchDashboardData([
            'warehouse'  => $this->testWarehouseId(),
            'date_start' => '2030-01-01',
            'date_end'   => '2030-01-31',
        ]);

        $this->assertSame('success', $json['status']);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('data', $json);

        foreach (self::PAYLOAD_KEYS as $key) {
            $this->assertArrayHasKey($key, $json['data'], "Missing payload key: {$key}");
        }

        $this->assertSame(
            ['operating', 'prepared', 'closed'],
            array_keys($json['data']['hub_stats'])
        );
        $this->assertSame(
            ['open_ticket', 'resolved', 'mttr', 'mttf'],
            array_keys($json['data']['support_performance'])
        );
        $this->assertSame($this->testWarehouseId(), $json['data']['net_profit'][0]['warehouse']);
    }

    // ------------------------------------------------------------------
    // 5. Hub stats
    // ------------------------------------------------------------------

    /** @test */
    public function test_hub_stats_counts_seeded_partners(): void
    {
        $before = $this->fetchDashboardData()['data']['hub_stats'];

        $this->seedOpportunityPartners();

        $after = $this->fetchDashboardData()['data']['hub_stats'];

        // Seeded: 4 ACTIVE (operating), 1 PREPARING, 1 INACTIVE (closed).
        $this->assertEquals($before['operating'] + 4, $after['operating']);
        $this->assertEquals($before['prepared'] + 1, $after['prepared']);
        $this->assertEquals($before['closed'] + 1, $after['closed']);
    }

    // ------------------------------------------------------------------
    // 6. Contract monitoring buckets
    // ------------------------------------------------------------------

    /** @test */
    public function test_contract_monitoring_buckets(): void
    {
        $before = $this->fetchDashboardData()['data']['contract_monitoring'];

        $this->seedOpportunityPartners();

        $after = $this->fetchDashboardData()['data']['contract_monitoring'];

        // Each bucket gains exactly one seeded ACTIVE-status partner.
        $this->assertEquals($before['active'] + 1, $after['active'], 'ACTIVE bucket');
        $this->assertEquals($before['renewal_soon'] + 1, $after['renewal_soon'], 'RENEWAL_SOON bucket');
        $this->assertEquals($before['expired'] + 1, $after['expired'], 'EXPIRED bucket');
    }

    // ------------------------------------------------------------------
    // 7. Ticket counters incl. month + warehouse scoping
    // ------------------------------------------------------------------

    /** @test */
    public function test_ticket_counters_month_and_warehouse_scoping(): void
    {
        $query = [
            'warehouse'  => $this->testWarehouseId(),
            'date_start' => $this->testTicketMonth().'-01',
            'date_end'   => $this->testTicketMonth().'-31',
        ];

        $before = $this->fetchDashboardData($query)['data'];

        if ($before['support_performance']['mttr'] != 0 || $before['support_performance']['mttf'] != 0) {
            $this->fail(sprintf(
                'Real ticket_log data found in %s - absolute MTTR/MTTF assertions are unsafe. '
                .'Pick another fixture month (see KNOWN-ISSUES.md KI-3).',
                $this->testTicketMonth()
            ));
        }

        $this->seedAftersalesTickets();

        $after = $this->fetchDashboardData($query)['data'];

        // Ticket counter query has NO warehouse filter: C2 (other warehouse,
        // in-month) is included here -> 4 open tickets + C2 = +5.
        $this->assertEquals($before['support_performance']['open_ticket'] + 5, $after['support_performance']['open_ticket']);
        $this->assertEquals($before['support_performance']['resolved'] + 1, $after['support_performance']['resolved']);

        // MTTR = avg of consecutive log pairs ((2h + 30h) / 2), MTTF = open->closed = 30h.
        $this->assertEquals(16, $after['support_performance']['mttr']);
        $this->assertEquals(30, $after['support_performance']['mttf']);

        // Category counts ARE warehouse-scoped, so only T1-T5 count.
        foreach ([
            'erp'          => 1,
            'operational'  => 1,
            'financial'    => 1,
            'partnership'  => 1,
            'other'        => 1,
        ] as $category => $expectedDelta) {
            $this->assertEquals(
                $before['support_request_count'][$category] + $expectedDelta,
                $after['support_request_count'][$category],
                "Category delta mismatch: {$category}"
            );
        }
    }

    // ------------------------------------------------------------------
    // 8. Missing warehouse param degrades gracefully (post-fix behaviour)
    // ------------------------------------------------------------------

    /** @test */
    public function test_missing_warehouse_param_degrades_gracefully(): void
    {
        // Before the graceful-degradation fix this combination produced a 500:
        // an undefined-index notice on $q["warehouse"] followed by a TypeError
        // from calculateMonthlyFinancialStatement(string ...).
        $json = $this->fetchDashboardData();

        $this->assertSame('success', $json['status']);
        $this->assertNull($json['data']['net_profit'][0]['warehouse']);

        foreach (self::PAYLOAD_KEYS as $key) {
            $this->assertArrayHasKey($key, $json['data'], "Missing payload key: {$key}");
        }
    }
}
