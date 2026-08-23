<?php

namespace Tests\Feature\Concerns;

use App\User;
use Illuminate\Support\Facades\DB;

/**
 * Transactional fixtures for the Aftersales dashboard integration tests.
 *
 * All inserts happen inside the per-test transaction opened by
 * DatabaseTransactions, so they never persist to the dev database.
 */
trait SeedsAftersalesFixtures
{
    /**
     * The warehouse id used by ticket fixtures. Chosen to be unique enough
     * to never collide with real GEU_Express_ID values in the dev database.
     */
    protected function testWarehouseId(): string
    {
        return 'W-TEST-1';
    }

    /**
     * Obscure month used for ticket fixtures. Assertions on ticket counters
     * assume no real production data exists in this month
     * (see KNOWN-ISSUES.md KI-3).
     */
    protected function testTicketMonth(): string
    {
        return '2030-01';
    }

    /**
     * Lazily resolves the acting user once per test.
     *
     * The `users` table uses the MyISAM engine, which does NOT participate
     * in transactions - rows inserted here would survive the per-test
     * rollback (see KNOWN-ISSUES.md KI-4). Therefore we never write to
     * `users`: instead we reuse an existing user of the database purely as
     * an authenticated identity. None of the tested endpoints write to
     * `users`, so this is side-effect free.
     */
    protected function testUser(): User
    {
        if ($this->user) {
            return $this->user;
        }

        $user = User::query()->orderBy('id')->first();

        if (!$user) {
            $this->markTestSkipped(
                'No user exists in the test database. Create at least one user to run these tests.'
            );
        }

        return $this->user = $user;
    }

    /**
     * Seeds opportunity_partner rows covering every hub-status and every
     * contract-monitoring bucket:
     *
     *   # | status     | contract_end_at      | contributes to
     *   1 | ACTIVE     | now + 1 year         | operating, contract ACTIVE
     *   2 | ACTIVE     | now - 1 month        | operating, contract EXPIRED
     *   3 | ACTIVE     | NULL                 | operating, contract UNKNOWN
     *   4 | ACTIVE     | now + 1 month        | operating, contract RENEWAL_SOON
     *   5 | PREPARING  | NULL                 | prepared
     *   6 | INACTIVE   | NULL                 | closed
     */
    protected function seedOpportunityPartners(): void
    {
        $now = now();

        $rows = [
            ['ACTIVE',    $now->copy()->addYear()->format('Y-m-d H:i:s')],
            ['ACTIVE',    $now->copy()->subMonth()->format('Y-m-d H:i:s')],
            ['ACTIVE',    null],
            ['ACTIVE',    $now->copy()->addMonth()->format('Y-m-d H:i:s')],
            ['PREPARING', null],
            ['INACTIVE',  null],
        ];

        foreach ($rows as $i => [$status, $contractEnd]) {
            DB::table('opportunity_partner')->insert([
                'full_name'       => 'Test Partner '.$i,
                'company_name'    => 'Test Company '.$i,
                'status'          => $status,
                'contract_end_at' => $contractEnd,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }

    /**
     * Seeds tickets (and their logs) for month 2030-01:
     *
     *   ticket | status | category    | created           | wh        | log pairs (hours)
     *   T1     | OPEN   | ERP         | 2030-01-02 08:00  | W-TEST-1  | OPEN->ON_PROGRESS = 2h
     *   T2     | CLOSED | Operational | 2030-01-05 09:00  | W-TEST-1  | OPEN->CLOSED      = 30h
     *   T3     | OPEN   | Financial   | 2030-01-07 08:00  | W-TEST-1  |
     *   T4     | OPEN   | Partnership | 2030-01-08 08:00  | W-TEST-1  |
     *   T5     | OPEN   | Other       | 2030-01-09 08:00  | W-TEST-1  |
     *   C1     | OPEN   | ERP         | 2030-02-02 08:00  | W-TEST-1  | excluded: other month
     *   C2     | OPEN   | ERP         | 2030-01-03 08:00  | OTHER-WH  | excluded from wh-scoped queries
     *
     * Expected deltas with ?warehouse=W-TEST-1&date_start=2030-01-01&date_end=2030-01-31:
     *   support_performance.open_ticket +5 (T1,T3,T4,T5,C2 - ticket counter has NO wh filter)
     *   support_performance.resolved    +1 (T2)
     *   support_request_count: erp+1 operational+1 financial+1 partnership+1 other+1
     *   mttr = 16 ((2+30)/2), mttf = 30
     */
    protected function seedAftersalesTickets(): void
    {
        $wh  = $this->testWarehouseId();
        $now = now();

        $tickets = [
            ['TESTAS-T1', 'OPEN',   'ERP',         '2030-01-02 08:00:00', $wh],
            ['TESTAS-T2', 'CLOSED', 'Operational', '2030-01-05 09:00:00', $wh],
            ['TESTAS-T3', 'OPEN',   'Financial',   '2030-01-07 08:00:00', $wh],
            ['TESTAS-T4', 'OPEN',   'Partnership', '2030-01-08 08:00:00', $wh],
            ['TESTAS-T5', 'OPEN',   'Other',       '2030-01-09 08:00:00', $wh],
            ['TESTAS-C1', 'OPEN',   'ERP',         '2030-02-02 08:00:00', $wh],
            ['TESTAS-C2', 'OPEN',   'ERP',         '2030-01-03 08:00:00', 'OTHER-WH'],
        ];

        foreach ($tickets as [$no, $status, $category, $created, $geuId]) {
            DB::table('ticket')->insert([
                'Ticket_No'       => $no,
                'Proposed_Date'   => substr($created, 0, 10),
                'Ticket_Type'     => 'INTEGRATION_TEST',
                'Ticket_Category' => $category,
                'Ticket_Subject'  => 'Aftersales dashboard test',
                'GEU_Express_ID'  => $geuId,
                'Description'     => 'Seeded by AftersalesDashboardTest',
                'Ticket_Status'   => $status,
                'Created_By'      => '__test_aftersales__',
                'Created_Date'    => $created,
                'Completion_Note' => $status === 'CLOSED' ? 'done' : null,
            ]);

            // One OPEN log per ticket; extra logs below create the timed pairs.
            DB::table('ticket_log')->insert($this->ticketLogRow($no, $category, $geuId, 'OPEN', $created));
        }

        DB::table('ticket_log')->insert(array_merge(
            [$this->ticketLogRow('TESTAS-T1', 'ERP', $wh, 'ON_PROGRESS', '2030-01-02 10:00:00')],
            [$this->ticketLogRow('TESTAS-T2', 'Operational', $wh, 'CLOSED', '2030-01-06 15:00:00')]
        ));
    }

    private function ticketLogRow(string $no, string $category, string $geuId, string $status, string $actionDate): array
    {
        return [
            'Ticket_No'       => $no,
            'Proposed_Date'   => substr($actionDate, 0, 10),
            'Ticket_Type'     => 'INTEGRATION_TEST',
            'Ticket_Category' => $category,
            'Ticket_Subject'  => 'Aftersales dashboard test',
            'GEU_Express_ID'  => $geuId,
            'Description'     => 'Log seeded by AftersalesDashboardTest',
            'Ticket_Status'   => $status,
            'Created_By'      => '__test_aftersales__',
            'Action_By'       => 777777,
            'Action_Date'     => $actionDate,
            'Action_Status'   => $status,
        ];
    }

    /**
     * Calls the data endpoint as the authenticated user and returns the full
     * decoded JSON body.
     */
    protected function fetchDashboardData(array $query = []): array
    {
        $response = $this->actingAs($this->testUser())
            ->get(self::DATA_URL.(empty($query) ? '' : '?'.http_build_query($query)));

        $response->assertStatus(200);

        return $response->json();
    }
}
