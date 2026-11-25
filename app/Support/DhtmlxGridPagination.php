<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class DhtmlxGridPagination
{
    public const ALLOWED_PAGE_SIZES = [10, 25, 50, 100];
    public const DEFAULT_PAGE_SIZE = 25;

    /**
     * Get validated page size from request.
     */
    public static function getPageSize(Request $request, int $default = self::DEFAULT_PAGE_SIZE): int
    {
        $raw = $request->query('count', $request->query('limit', $request->query('per_page')));
        if ($raw === null || $raw === '') {
            return $default;
        }

        $size = (int) $raw;
        if (in_array($size, self::ALLOWED_PAGE_SIZES, true)) {
            return $size;
        }

        return $size > 0 ? min($size, 500) : $default;
    }

    /**
     * Get posStart (offset) from request.
     */
    public static function getPosStart(Request $request, int $pageSize = self::DEFAULT_PAGE_SIZE): int
    {
        if ($request->filled('posStart')) {
            return max(0, (int) $request->query('posStart'));
        }

        if ($request->filled('page')) {
            $page = max(1, (int) $request->query('page'));
            return ($page - 1) * $pageSize;
        }

        return 0;
    }

    /**
     * Apply pagination and optional sorting to an Eloquent or Query Builder.
     *
     * @param EloquentBuilder|QueryBuilder $query
     */
    public static function paginateQuery($query, Request $request, array $options = []): array
    {
        $pageSize = self::getPageSize($request, $options['default_page_size'] ?? self::DEFAULT_PAGE_SIZE);
        $posStart = self::getPosStart($request, $pageSize);

        // Filtering (filter[colIdx]=value from initCnsGridPagination)
        $filters = $request->query('filter', []);
        if (is_array($filters) && isset($options['filter_map']) && is_array($options['filter_map'])) {
            foreach ($filters as $colIdx => $rawValue) {
                if ($rawValue === null) {
                    continue;
                }
                $term = trim((string) $rawValue);
                if ($term === '') {
                    continue;
                }
                $colIdx = (int) $colIdx;
                if (!isset($options['filter_map'][$colIdx])) {
                    continue;
                }
                $target = $options['filter_map'][$colIdx];
                if (is_callable($target)) {
                    $target($query, $term);
                } elseif (is_string($target) && $target !== '') {
                    $query->where($target, 'like', '%' . $term . '%');
                }
            }
        }

        // Sorting
        if ($request->filled('sort_col') && isset($options['col_map'])) {
            $colIdx = (int) $request->query('sort_col');
            $direction = strtolower((string) $request->query('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
            if (isset($options['col_map'][$colIdx])) {
                $dbCol = $options['col_map'][$colIdx];
                if (is_string($dbCol) && $dbCol !== '') {
                    $query->orderBy($dbCol, $direction);
                }
            }
        }

        // Total count before limit/offset
        $total = (clone $query)->count();

        // Slice rows
        $rows = $query->offset($posStart)->limit($pageSize)->get();

        return [
            'rows'     => $rows,
            'total'    => (int) $total,
            'posStart' => $posStart,
            'count'    => $pageSize,
        ];
    }

    /**
     * Paginate an array or Laravel Collection in memory.
     *
     * @param array|Collection $items
     */
    public static function paginateCollection($items, Request $request, array $options = []): array
    {
        $pageSize = self::getPageSize($request, $options['default_page_size'] ?? self::DEFAULT_PAGE_SIZE);
        $posStart = self::getPosStart($request, $pageSize);

        if ($items instanceof Collection) {
            $total = $items->count();
            $array = $items->values()->all();
        } else {
            $total = count($items);
            $array = array_values($items);
        }

        // Sorting if provided via callback
        if (isset($options['sort_callback']) && is_callable($options['sort_callback'])) {
            usort($array, $options['sort_callback']);
        }

        $sliced = array_slice($array, $posStart, $pageSize);

        return [
            'rows'     => $sliced,
            'total'    => $total,
            'posStart' => $posStart,
            'count'    => $pageSize,
        ];
    }

    /**
     * Build standard DHTMLX XML header.
     */
    public static function header(int $total, int $posStart, array $userData = []): string
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<rows total_count=\"{$total}\" pos=\"{$posStart}\">\n";
        foreach ($userData as $key => $val) {
            $xml .= "  <userdata name=\"" . htmlspecialchars((string) $key, ENT_QUOTES) . "\">" . htmlspecialchars((string) $val, ENT_QUOTES) . "</userdata>\n";
        }
        return $xml;
    }

    /**
     * Build standard DHTMLX XML footer.
     */
    public static function footer(): string
    {
        return "</rows>";
    }

    /**
     * Wrap XML rows and return an HTTP response.
     */
    public static function response(int $total, int $posStart, string $bodyXml, array $userData = []): Response
    {
        $xml = self::header($total, $posStart, $userData);
        $xml .= $bodyXml;
        $xml .= (substr($bodyXml, -1) === "\n" ? "" : "\n") . self::footer();

        return response($xml, 200, ['Content-Type' => 'text/xml']);
    }
}
