# Known Issues — Business Partner › Dashboard › Aftersales

Behaviours that are intentionally **documented, not fixed** by
`AftersalesDashboardTest`. Each issue is referenced from the relevant test via
`@group known-issue` docblocks / KI-id mentions.

| ID  | Title | Where | Status |
|-----|-------|-------|--------|
| KI-1 | SQL interpolation in ticket-log queries | `app/Http/Controllers/Partner/SalesController.php` (interpolated `$dateFilter` / `$warehouseFilter`) | open — security TODO |
| KI-2 | Placeholder dashboard blocks | same controller (`performance`, `complaints_list`) | open — feature TODO |
| KI-3 | Test fixtures assume empty fixture month | `tests/Feature/BusinessPartner/AftersalesDashboardTest.php` (month `2030-01`) | assumption |
| KI-4 | `users` table is MyISAM (non-transactional) | dev database schema | open — schema debt |

---

## KI-1 · SQL interpolation in ticket-log query

**Where:** `SalesController::aftersalesDashboardData()` — the MTTR/MTTF
subquery builds its WHERE clause by string interpolation:

```php
$dateFilter      = $carbonDate ? "AND DATE_FORMAT(tl_open.Action_Date, '%Y-%m') = '$carbonDate'" : '';
$warehouseFilter = $whid ? "AND tl_open.GEU_Express_ID = '$whid'" : '';
```

`$whid` comes straight from the query string and is concatenated into
`selectRaw()`. `$carbonDate` is lower risk (always normalized to `Y-m` via
Carbon), but the pattern is unsafe.

**Impact:** potential SQL injection through the `warehouse` query parameter of
`GET /partner-dashboard-aftersales-data`.

**Why open:** fixing requires parameterizing the raw subquery; out of scope for
the integration-test task.

**Test coverage:** none of the passing tests depends on this path. A dedicated
security test would be the natural follow-up after parameterization.

---

## KI-2 · Placeholder dashboard blocks

**Where:** payload assembly in `SalesController::aftersalesDashboardData()`

- `'performance' => ['high' => 0, 'medium' => 0, 'low' => 0]` — hardcoded zeros
- `'complaints_list' => []` — always empty

**Impact:** the UI sections fed by these keys can never show real data.

**Test coverage:** structure-only (`assertArrayHasKey`). No value assertions,
so the tests stay green when these get implemented — at which point new value
tests should be added.

---

## KI-3 · Fixture-month assumption

`test_ticket_counters_month_and_warehouse_scoping` asserts **absolute**
MTTR (16h) / MTTF (30h) values for month `2030-01`. If real production data
ever appears in that month, the guard inside the test fails with an explicit
message instead of a confusing number mismatch. Remedy: move the fixture month
constant (`SeedsAftersalesFixtures::testTicketMonth()`) further into the future.

---

## KI-4 · `users` table uses the MyISAM engine

**Where:** dev database `erp_ksps`, table `users` (`SHOW TABLE STATUS LIKE 'users'`).

MyISAM does not participate in transactions: rows inserted inside the per-test
transaction survive the rollback silently. All other tables involved here
(`user_logs`, `opportunity_partner`, `ticket`, `ticket_log`) are InnoDB.

**Impact:** any test fixture written to `users` would leak into the dev
database. The trait therefore never inserts users — it reuses an existing user
as the authenticated identity (`SeedsAftersalesFixtures::testUser()`). If the
database has no user at all, tests skip with an explanatory message.

**Why open:** converting the table (`ALTER TABLE users ENGINE=InnoDB`) touches
shared dev data and is a deliberate schema-migration decision, out of scope
here.

---

## Fixed alongside this test suite

For context — these were broken before the test task and were fixed as part of
it (approved scope):

1. Route/method mismatch: route pointed to `aftersalesDashboardData` while the
   controller method was named `afterSalesDashboardData`
   → endpoint threw at dispatch. Renamed the method.
2. Missing `warehouse` query param produced a fatal undefined-index notice plus
   a `TypeError` from `calculateMonthlyFinancialStatement(string ...)`
   → now returns HTTP 200 with warehouse-scoped filters simply unset
   (`net_profit[0].warehouse === null`). Covered by
   `test_missing_warehouse_param_degrades_gracefully`.
