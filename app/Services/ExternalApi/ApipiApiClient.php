<?php

namespace App\Services\ExternalApi;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Typed client for the apipi pickup API data endpoints.
 *
 * Wraps ApipiAuthService (auth + cookies) and adds:
 *  - request throttle (APIPI_THROTTLE_MS) so we do not hammer the provider
 *  - retry with exponential backoff on HTTP 429 / 5xx
 *
 * No network calls are made at construction time.
 */
class ApipiApiClient
{
    /** @var ApipiAuthService */
    protected $auth;
    /** @var int */
    protected $throttleMs;
    /** @var int */
    protected $maxRetries = 4;

    public function __construct(ApipiAuthService $auth)
    {
        $this->auth = $auth;
        $this->throttleMs = (int) config('services.apipi.throttle_ms', 300);
    }

    /* ---------------------------------------------------------------- HTTP */

    /**
     * GET with throttle + retry, returns the raw response envelope.
     */
    public function rawGet(string $path, array $query = []): array
    {
        $attempt = 0;

        while (true) {
            $res    = $this->auth->get($path, $query);
            $status = (int) $res['status'];

            if ($status === 429 || $status >= 500) {
                $attempt++;
                if ($attempt > $this->maxRetries) {
                    throw new \RuntimeException("Apipi GET {$path} failed after {$attempt} attempts (HTTP {$status})");
                }
                // exponential backoff: 1s, 2s, 4s, 8s
                usleep((int) (1000 * 1000 * pow(2, $attempt - 1)));
                continue;
            }

            $this->throttle();

            return $res;
        }
    }

    /**
     * GET returning the decoded "data" payload.
     */
    public function get(string $path, array $query = []): array
    {
        $res = $this->rawGet($path, $query);

        if ((int) $res['status'] !== 200 || !is_array($res['body'])) {
            throw new \RuntimeException("Apipi {$path} unexpected response (HTTP {$res['status']})");
        }

        return $res['body']['data'] ?? [];
    }

    protected function throttle(): void
    {
        if ($this->throttleMs > 0) {
            usleep($this->throttleMs * 1000);
        }
    }

    /* ----------------------------------------------------------- Endpoints */

    /** Cursor-paginated supplier list. @return array{data:array,next_cursor:?int,has_next:bool,total_records:int} */
    public function suppliers(int $limit = 200, $cursor = null, ?string $search = null, ?string $jenis = null): array
    {
        $q = ['limit' => $limit];
        if ($cursor !== null && $cursor !== '') { $q['cursor'] = $cursor; }
        if ($search !== null) { $q['search'] = $search; }
        if ($jenis !== null) { $q['jenis'] = $jenis; }

        return $this->get('/api/suppliers', $q);
    }

    public function supplier($id): array
    {
        return $this->get('/api/suppliers/' . $id);
    }

    /** Page-paginated work order list. */
    public function workOrders(int $page = 1, int $limit = 100): array
    {
        return $this->get('/api/work-orders', ['page' => $page, 'limit' => $limit]);
    }

    public function workOrder($id): array
    {
        return $this->get('/api/work-orders/' . $id);
    }

    /** Page-paginated surat jalan list. */
    public function suratJalan(int $page = 1, int $limit = 100, ?string $status = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $q = ['page' => $page, 'limit' => $limit];
        if ($status !== null) { $q['status'] = $status; }
        if ($dateFrom !== null) { $q['date_from'] = $dateFrom; }
        if ($dateTo !== null) { $q['date_to'] = $dateTo; }

        return $this->get('/api-tms/surat-jalan', $q);
    }

    public function pickup($id): array
    {
        return $this->get('/api-tms/pickups/' . $id);
    }

    public function suratJalanDetail($id): array
    {
        return $this->get('/api-tms/surat-jalan/' . $id);
    }

    public function detailPhotos($detailId): array
    {
        return $this->get('/api-tms/surat-jalan/detail/' . $detailId . '/photos');
    }

    /** Page-paginated movement list. */
    public function movements(int $page = 1, int $limit = 100): array
    {
        return $this->get('/api-tms/movements', ['page' => $page, 'limit' => $limit]);
    }

    public function movementDetail($id): array
    {
        return $this->get('/api-tms/movements/' . $id);
    }

    /**
     * All non-deleted movements destined for Pelindo (tujuan_gudang contains "Pelindo").
     */
    public function pelindoInboundMovements(): array
    {
        $matched = [];
        $page = 1;

        do {
            $payload = $this->movements($page, 100);
            $rows = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : $payload;

            foreach ($rows as $row) {
                if (
                    is_array($row)
                    && trim((string) ($row['deleted_at'] ?? '')) === ''
                    && stripos((string) ($row['tujuan_gudang'] ?? ''), 'Pelindo') !== false
                ) {
                    $matched[] = $row;
                }
            }

            $hasNext = !empty($payload['pagination']['has_next']);
            $page++;
        } while ($hasNext && $page <= 20);

        return $matched;
    }

    /** Page-paginated warehouse list. */
    public function warehouses(int $page = 1, int $limit = 200, ?string $type = null, ?string $search = null): array
    {
        $q = ['page' => $page, 'limit' => $limit];
        if ($type !== null && $type !== '') { $q['type'] = $type; }
        if ($search !== null && $search !== '') { $q['search'] = $search; }

        return $this->get('/api/warehouses', $q);
    }

    /**
     * Active warehouses cached for 15 minutes with stale-cache fallback on API failure.
     */
    public function cachedWarehouses(?string $type = null, ?string $search = null): array
    {
        $all = Cache::get('apipi.warehouses.all');

        if (!is_array($all)) {
            try {
                $all = [];
                $page = 1;
                do {
                    $payload = $this->warehouses($page, 200);
                    $rows = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : $payload;
                    foreach ($rows as $row) {
                        $isDeleted = !empty($row['deleted_at']) && trim((string) $row['deleted_at']) !== '';
                        if (is_array($row) && (!isset($row['active']) || (int) $row['active'] === 1) && !$isDeleted) {
                            $all[] = $row;
                        }
                    }
                    $hasNext = !empty($payload['pagination']['has_next']);
                    $page++;
                } while ($hasNext && $page <= 20);

                usort($all, function ($a, $b) {
                    return strnatcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
                });

                Cache::put('apipi.warehouses.all', $all, now()->addMinutes(15));
                Cache::put('apipi.warehouses.stale', $all, now()->addDays(7));
            } catch (\Throwable $e) {
                Log::warning('Apipi warehouses fetch failed: ' . $e->getMessage());
                $stale = Cache::get('apipi.warehouses.stale', []);
                $all = is_array($stale) ? $stale : [];
                // Short backoff cache so a down upstream does not stall every request
                Cache::put('apipi.warehouses.all', $all, now()->addSeconds(60));
            }
        }

        return $this->filterWarehouses($all, $type, $search);
    }

    /**
     * Report warehouse list: active warehouses with type=site plus Surabaya.
     */
    public function reportWarehouses(?string $search = null): array
    {
        return array_values(array_filter($this->cachedWarehouses(null, $search), function ($row) {
            $rowType = strtolower(trim((string) ($row['type'] ?? '')));
            $id = isset($row['id']) ? (int) $row['id'] : 0;
            $name = trim((string) ($row['name'] ?? ''));

            return $rowType === 'site' || $id === 5 || strcasecmp($name, 'Surabaya') === 0;
        }));
    }

    protected function filterWarehouses(array $rows, ?string $type = null, ?string $search = null): array
    {
        $allowedTypes = [];
        if ($type !== null && trim($type) !== '') {
            $allowedTypes = array_values(array_filter(array_map(function ($t) {
                return strtolower(trim($t));
            }, explode(',', $type))));
        }

        $q = $search !== null ? strtolower(trim($search)) : '';

        if ($allowedTypes === [] && $q === '') {
            return array_values($rows);
        }

        return array_values(array_filter($rows, function ($row) use ($allowedTypes, $q) {
            if ($allowedTypes !== []) {
                $rowType = strtolower(trim((string) ($row['type'] ?? '')));
                if (!in_array($rowType, $allowedTypes, true)) {
                    return false;
                }
            }
            if ($q !== '') {
                $name = strtolower((string) ($row['name'] ?? ''));
                if (strpos($name, $q) === false) {
                    return false;
                }
            }
            return true;
        }));
    }
}
