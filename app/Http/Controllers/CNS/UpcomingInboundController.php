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

        $movements = Cache::get($cacheKey);
        if (!is_array($movements)) {
            try {
                $movements = app(ApipiApiClient::class)->pelindoInboundMovements();
                Cache::put($cacheKey, $movements, now()->addMinutes(2));
            } catch (Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data movements dari APIPI: ' . $e->getMessage(),
                ], 502);
            }
        }

        $usedKodes = [];
        foreach (UCOStoragePelindo::whereNotNull('Reference_Number')->where('Reference_Number', '!=', '')->pluck('Reference_Number') as $ref) {
            $usedKodes[strtoupper(trim((string) $ref))] = true;
        }

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

            $tglFormatted = '-';
            $tglDate = '';
            if (!empty($m['tgl'])) {
                try {
                    $parsed = Carbon::parse($m['tgl']);
                    $tglDate = $parsed->toDateString();
                    $tglFormatted = $parsed->format('d/m/Y');
                } catch (Throwable $e) {
                    $tglDate = '';
                    $tglFormatted = '-';
                }
            }

            if ($startDate !== '' && ($tglDate === '' || $tglDate < $startDate)) {
                $dateExcluded++;
                continue;
            }
            if ($endDate !== '' && ($tglDate === '' || $tglDate > $endDate)) {
                $dateExcluded++;
                continue;
            }

            $dariGudang   = trim((string) ($m['dari_gudang'] ?? ''));
            $tujuanGudang = trim((string) ($m['tujuan_gudang'] ?? ''));

            if ($search !== '') {
                $haystack = $kode . ' ' . $dariGudang . ' ' . $tujuanGudang . ' ' . $status;
                if (stripos($haystack, $search) === false) {
                    $searchExcluded++;
                    continue;
                }
            }

            $filtered[] = [
                'id'            => (string) ($m['id'] ?? $kode),
                'kode'          => $kode,
                'tgl'           => $tglFormatted,
                'dari_gudang'   => $dariGudang !== '' ? $dariGudang : '-',
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
            $xml .= '<cell>' . $this->escapeXml($row['dari_gudang']) . '</cell>';
            $xml .= '<cell>' . $this->escapeXml($row['tujuan_gudang']) . '</cell>';
            $xml .= '<cell>' . $row['qty'] . '</cell>';
            $xml .= '<cell>' . $this->escapeXml($row['status']) . '</cell>';
            $xml .= "</row>\n";
        }

        $xml .= '</rows>';

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
