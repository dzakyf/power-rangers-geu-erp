<?php

namespace App\Services\ExternalApi;

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
}
