<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\Services\ExternalApi\ApipiApiClient;
use App\UCOStoragePelindo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpcomingInboundController extends Controller
{
    /**
     * DHTMLX Grid XML data feed for upcoming inbound movements from APIPI.
     */
    public function xmlData(Request $request)
    {
        $cacheKey = 'cns.upcoming_inbound.movements';
        $forceRefresh = filter_var($request->query('refresh', false), FILTER_VALIDATE_BOOLEAN);
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        $apiClient = app(ApipiApiClient::class);
        $movements = Cache::get($cacheKey);
        if (!is_array($movements)) {
            try {
                $movements = $apiClient->pelindoInboundMovements();
                Cache::put($cacheKey, $movements, now()->addMinutes(2));
            } catch (Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data movements dari APIPI: ' . $e->getMessage(),
                ], 502);
            }
        }

        $usedKodes = [];
        try {
            foreach (UCOStoragePelindo::whereNotNull('Reference_Number')->where('Reference_Number', '!=', '')->pluck('Reference_Number') as $ref) {
                $usedKodes[strtoupper(trim((string) $ref))] = true;
            }
        } catch (Throwable $e) {
            $usedKodes = [];
        }

        $dateType  = strtolower(trim((string) $request->query('date_type', 'eta')));
        $startDate = trim((string) $request->query('start_date', ''));
        $endDate   = trim((string) $request->query('end_date', ''));
        $search    = trim((string) $request->query('search', ''));

        $allowedPerPage = [10, 25, 50, 100];
        $perPage = (int) $request->query('per_page', 25);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 25;
        }

        $sourceCount = is_array($movements) ? count($movements) : 0;
        $usedExcluded = 0;
        $doneExcluded = 0;
        $dateExcluded = 0;
        $searchExcluded = 0;

        $filtered = [];
        foreach ($movements as $m) {
            if (!is_array($m)) {
                continue;
            }

            $kode = trim((string) ($m['kode'] ?? ''));
            if ($kode === '') {
                continue;
            }
            if (isset($usedKodes[strtoupper($kode)])) {
                $usedExcluded++;
                continue;
            }

            $status = trim((string) ($m['logistics_status'] ?? ''));
            if (strtolower($status) === 'done') {
                $doneExcluded++;
                continue;
            }

            $movId = $m['id'] ?? $kode;
            $rawDariGudang = trim((string) ($m['dari_gudang'] ?? ''));
            $details = $this->resolveMovementDetails($movId, $m, $apiClient, $forceRefresh);
            $dateInfo = $this->resolveMovementDates($details, $rawDariGudang);

            $tglFormatted = $dateInfo['tgl_formatted'];
            $tglDate      = $dateInfo['tgl_date'];
            $etaFormatted = $dateInfo['eta_formatted'];
            $etaDate      = $dateInfo['eta_date'];
            $dariGudang   = $dateInfo['dari_gudang'];

            $filterDate = in_array($dateType, ['movement', 'loading', 'tgl_loading'], true) ? $tglDate : $etaDate;
            if ($startDate !== '' && ($filterDate === '' || $filterDate < $startDate)) {
                $dateExcluded++;
                continue;
            }
            if ($endDate !== '' && ($filterDate === '' || $filterDate > $endDate)) {
                $dateExcluded++;
                continue;
            }

            $tujuanGudang = trim((string) ($m['tujuan_gudang'] ?? ''));

            if ($search !== '') {
                $haystack = $kode . ' ' . $dariGudang . ' ' . $tujuanGudang . ' ' . $status . ' ' . $etaFormatted;
                if (stripos($haystack, $search) === false) {
                    $searchExcluded++;
                    continue;
                }
            }

            $filtered[] = [
                'id'            => (string) $movId,
                'kode'          => $kode,
                'tgl'           => $tglFormatted,
                'eta'           => $etaFormatted,
                'dari_gudang'   => $dariGudang,
                'tujuan_gudang' => $tujuanGudang !== '' ? $tujuanGudang : '-',
                'qty'           => number_format((float) ($m['total_qty'] ?? 0), 2, '.', ','),
                'status'        => $status !== '' ? $status : '-',
            ];
        }

        $filteredCount = count($filtered);
        $totalCount = $filteredCount;

        if ($sourceCount === 0 || $filteredCount === 0) {
            Log::info('CNS upcoming inbound empty', [
                'source_count'   => $sourceCount,
                'used_excluded'  => $usedExcluded,
                'done_excluded'  => $doneExcluded,
                'date_excluded'  => $dateExcluded,
                'search_excluded'=> $searchExcluded,
                'filtered_count' => $filteredCount,
                'has_date_filter'=> ($startDate !== '' || $endDate !== ''),
                'has_search'     => $search !== '',
            ]);
        }
        $totalPages = $totalCount > 0 ? (int) ceil($totalCount / $perPage) : 1;
        $page = max(1, (int) $request->query('page', 1));

        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;
        $pagedRows = array_slice($filtered, $offset, $perPage);

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= '<rows total_count="' . $totalCount . '" page="' . $page . '" per_page="' . $perPage . '" total_pages="' . $totalPages . '"'
            . ' source_count="' . $sourceCount . '" used_excluded="' . $usedExcluded . '" done_excluded="' . $doneExcluded . '"'
            . ' date_excluded="' . $dateExcluded . '" search_excluded="' . $searchExcluded . '" filtered_count="' . $filteredCount . "\">\n";

        $no = $offset + 1;
        foreach ($pagedRows as $row) {
            $xml .= '<row id="' . $this->escapeXml($row['id']) . '">';
            $xml .= '<cell>' . $no++ . '</cell>';
            $xml .= '<cell>' . $this->escapeXml($row['kode']) . '</cell>';
            $xml .= '<cell>' . $this->escapeXml($row['tgl']) . '</cell>';
            $xml .= '<cell>' . $this->escapeXml($row['eta']) . '</cell>';
            $xml .= '<cell>' . $this->escapeXml($row['dari_gudang']) . '</cell>';
            $xml .= '<cell>' . $this->escapeXml($row['tujuan_gudang']) . '</cell>';
            $xml .= '<cell>' . $row['qty'] . '</cell>';
            $xml .= '<cell>' . $this->escapeXml($row['status']) . '</cell>';
            $xml .= "</row>\n";
        }

        $xml .= '</rows>';

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    /**
     * Resolve movement details from payload or via API call.
     */
    private function resolveMovementDetails($movId, array $mov, ApipiApiClient $apiClient, bool $forceRefresh = false): array
    {
        if (!empty($mov['details']) && is_array($mov['details'])) {
            return $mov['details'];
        }
        if (array_key_exists('warehouse_from_id', $mov) || array_key_exists('tgl_loading', $mov)) {
            return [$mov];
        }

        $cacheKey = 'cns.upcoming_inbound.detail.' . $movId;
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        $payload = Cache::remember($cacheKey, now()->addHours(1), function () use ($apiClient, $movId) {
            try {
                return $apiClient->movementDetail($movId);
            } catch (Throwable $e) {
                return null;
            }
        });

        if (is_array($payload)) {
            $details = isset($payload['details']) && is_array($payload['details']) ? $payload['details'] : [];
            if (!empty($details)) {
                return $details;
            }
        }

        return [];
    }

    /**
     * Resolve date, ETA, and sorted origin warehouses across all details of a movement.
     * Selects detail with the latest tgl_loading (tgl_loading terakhir) for ETA & Tanggal.
     * Orders warehouse origins in Asal Gudang chronologically ascending by tgl_loading.
     */
    private function resolveMovementDates(array $details, string $defaultDariGudang = ''): array
    {
        $detailsWithLoading = [];
        $detailsWithoutLoading = [];
        $hasGroupWhWithoutLoading = false;

        foreach ($details as $d) {
            if (!is_array($d)) {
                continue;
            }

            $whId = $d['warehouse_from_id'] ?? null;
            if ($whId !== null && is_numeric($whId)) {
                $whId = (int) $whId;
            }

            $whName = trim((string) ($d['gudang_from_name'] ?? ($d['dari_gudang'] ?? '')));
            $sjRaw  = !empty($d['sj_generated_at']) ? trim((string) $d['sj_generated_at']) : '';
            $sjTime = '';
            if ($sjRaw !== '') {
                try {
                    $sjTime = Carbon::parse($sjRaw)->toDateTimeString();
                } catch (Throwable $e) {
                    $sjTime = $sjRaw;
                }
            }

            $tglLoading = !empty($d['tgl_loading']) ? trim((string) $d['tgl_loading']) : '';
            if ($tglLoading !== '') {
                try {
                    $p = Carbon::parse($tglLoading);
                    $loadingDate = $p->toDateString();
                    $loadingFormatted = $p->format('d/m/Y');
                    $detailsWithLoading[] = [
                        'warehouse_from_id' => $whId,
                        'warehouse_name'    => $whName,
                        'loading_date'      => $loadingDate,
                        'loading_formatted' => $loadingFormatted,
                        'sj_generated_at'   => $sjTime,
                    ];
                } catch (Throwable $e) {
                    $detailsWithoutLoading[] = [
                        'warehouse_from_id' => $whId,
                        'warehouse_name'    => $whName,
                        'sj_generated_at'   => $sjTime,
                    ];
                }
            } else {
                $detailsWithoutLoading[] = [
                    'warehouse_from_id' => $whId,
                    'warehouse_name'    => $whName,
                    'sj_generated_at'   => $sjTime,
                ];
                if ($whId !== null && (
                    in_array($whId, [5, 18, 33, 48, 50], true) ||
                    in_array($whId, [30, 31], true) ||
                    in_array($whId, [24, 29, 37, 38, 40], true)
                )) {
                    $hasGroupWhWithoutLoading = true;
                }
            }
        }

        // Determine sorted origins (Asal Gudang) ascending by tgl_loading, then sj_generated_at
        $sortedOriginNames = [];
        if (!empty($detailsWithLoading)) {
            $ascending = $detailsWithLoading;
            usort($ascending, function ($a, $b) {
                $cmp = strcmp($a['loading_date'], $b['loading_date']);
                if ($cmp !== 0) {
                    return $cmp;
                }
                return strcmp($a['sj_generated_at'], $b['sj_generated_at']);
            });
            foreach ($ascending as $item) {
                if ($item['warehouse_name'] !== '') {
                    $sortedOriginNames[] = $item['warehouse_name'];
                }
            }
        }
        foreach ($detailsWithoutLoading as $item) {
            if ($item['warehouse_name'] !== '') {
                $sortedOriginNames[] = $item['warehouse_name'];
            }
        }

        $uniqueOrigins = [];
        foreach ($sortedOriginNames as $name) {
            if (empty($uniqueOrigins) || end($uniqueOrigins) !== $name) {
                $uniqueOrigins[] = $name;
            }
        }

        $dariGudangSorted = !empty($uniqueOrigins) ? implode(', ', $uniqueOrigins) : $defaultDariGudang;
        if ($dariGudangSorted === '') {
            $dariGudangSorted = '-';
        }

        if (!empty($detailsWithLoading)) {
            // Sort descending by loading_date, then sj_generated_at to pick the latest detail
            usort($detailsWithLoading, function ($a, $b) {
                $cmp = strcmp($b['loading_date'], $a['loading_date']);
                if ($cmp !== 0) {
                    return $cmp;
                }
                return strcmp($b['sj_generated_at'], $a['sj_generated_at']);
            });

            $latest = $detailsWithLoading[0];
            $whId = $latest['warehouse_from_id'];
            $daysToAdd = null;
            if (in_array($whId, [5, 18, 33, 48, 50], true)) {
                $daysToAdd = 1;
            } elseif (in_array($whId, [30, 31], true)) {
                $daysToAdd = 2;
            } elseif (in_array($whId, [24, 29, 37, 38, 40], true)) {
                $daysToAdd = 3;
            }

            if ($daysToAdd !== null) {
                $etaCarbon = Carbon::parse($latest['loading_date'])->addDays($daysToAdd);
                return [
                    'tgl_formatted' => $latest['loading_formatted'],
                    'tgl_date'      => $latest['loading_date'],
                    'eta_formatted' => $etaCarbon->format('d/m/Y'),
                    'eta_date'      => $etaCarbon->toDateString(),
                    'dari_gudang'   => $dariGudangSorted,
                ];
            }

            return [
                'tgl_formatted' => $latest['loading_formatted'],
                'tgl_date'      => $latest['loading_date'],
                'eta_formatted' => '-',
                'eta_date'      => '',
                'dari_gudang'   => $dariGudangSorted,
            ];
        }

        if ($hasGroupWhWithoutLoading) {
            return [
                'tgl_formatted' => '-',
                'tgl_date'      => '',
                'eta_formatted' => 'TBD',
                'eta_date'      => '',
                'dari_gudang'   => $dariGudangSorted,
            ];
        }

        return [
            'tgl_formatted' => '-',
            'tgl_date'      => '',
            'eta_formatted' => '-',
            'eta_date'      => '',
            'dari_gudang'   => $dariGudangSorted,
        ];
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
