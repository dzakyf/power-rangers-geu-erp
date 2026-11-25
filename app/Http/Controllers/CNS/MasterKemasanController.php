<?php

namespace App\Http\Controllers\CNS;

use App\CnsInventoryKemasan;
use App\CnsKemasanSerialNumber;
use App\CnsMasterKemasan;
use App\Http\Controllers\Controller;
use App\Support\DhtmlxGridPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class MasterKemasanController extends Controller
{
    public function index()
    {
        return view('cns.master.kemasan');
    }

    public function xmlData(Request $request)
    {
        $name = trim((string) $request->query('name'));
        $hasSerialTable = Schema::hasTable('cns_kemasan_serial_numbers');

        $query = CnsMasterKemasan::query();
        if ($hasSerialTable) {
            $query->with('availableSerialNumbers');
        }
        if ($name !== '') {
            $query->where('Name', 'like', "%{$name}%");
        }
        if (!$request->filled('sort_col')) {
            $query->orderBy('Name', 'asc');
        }

        $filterMap = [
            1  => 'Kemasan_ID',
            2  => 'Name',
            3  => 'product_type',
            4  => 'Volume',
            5  => function ($q, string $term) {
                $lower = strtolower($term);
                if (str_contains('ampas', $lower)) {
                    $q->where(function ($sub) {
                        $sub->where('Is_Ampas', 1)->orWhere('Name', 'like', '%ampas%');
                    });
                } elseif (str_contains('kemasan', $lower)) {
                    $q->where(function ($sub) {
                        $sub->whereNull('Is_Ampas')->orWhere('Is_Ampas', 0);
                    })->where('Name', 'not like', '%ampas%');
                }
            },
            6  => 'Max_Stock',
            7  => 'Unit',
            8  => 'Price',
            10 => 'created_at',
            11 => 'updated_at',
        ];
        if ($hasSerialTable) {
            $filterMap[9] = function ($q, string $term) {
                $q->whereHas('availableSerialNumbers', function ($sub) use ($term) {
                    $sub->where('serial_number', 'like', '%' . $term . '%');
                });
            };
        }

        $paginated = DhtmlxGridPagination::paginateQuery($query, $request, [
            'default_page_size' => 25,
            'col_map' => [
                1  => 'Kemasan_ID',
                2  => 'Name',
                3  => 'product_type',
                4  => 'Volume',
                5  => 'Is_Ampas',
                6  => 'Max_Stock',
                7  => 'Unit',
                8  => 'Price',
                10 => 'created_at',
                11 => 'updated_at',
            ],
            'filter_map' => $filterMap,
        ]);

        $bodyXml = '';
        $no = $paginated['posStart'] + 1;
        foreach ($paginated['rows'] as $item) {
            $bodyXml .= "<row id=\"{$item->Kemasan_ID}\">";
            $bodyXml .= "<cell>{$no}</cell>";
            $bodyXml .= "<cell>{$item->Kemasan_ID}</cell>";
            $bodyXml .= "<cell>" . htmlspecialchars((string) $item->Name) . "</cell>";
            $bodyXml .= "<cell>" . htmlspecialchars((string) ($item->product_type ?: 'Kemasan')) . "</cell>";
            $bodyXml .= "<cell>" . number_format((float) $item->Volume, 2, '.', ',') . "</cell>";
            $bodyXml .= "<cell>" . ($item->isAmpas() ? 'Ampas' : 'Kemasan') . "</cell>";
            $bodyXml .= "<cell>" . ($item->isAmpas() || $item->Max_Stock === null ? '-' : number_format((float) $item->Max_Stock, 2, '.', ',')) . "</cell>";
            $bodyXml .= "<cell>" . ($item->Unit !== null && $item->Unit !== '' ? htmlspecialchars((string) $item->Unit) : '-') . "</cell>";
            $bodyXml .= "<cell>" . number_format((float) ($item->Price ?? 0), 2, '.', ',') . "</cell>";
            $serialNumbers = $item->relationLoaded('availableSerialNumbers')
                ? $item->availableSerialNumbers->pluck('serial_number')->implode(', ')
                : '';
            $bodyXml .= "<cell>" . ($serialNumbers !== '' ? htmlspecialchars($serialNumbers) : '-') . "</cell>";
            $bodyXml .= "<cell>" . ($item->created_at ? $item->created_at->format('d/m/Y H:i') : '-') . "</cell>";
            $bodyXml .= "<cell>" . ($item->updated_at ? $item->updated_at->format('d/m/Y H:i') : '-') . "</cell>";
            $bodyXml .= "</row>";
            $no++;
        }

        return DhtmlxGridPagination::response($paginated['total'], $paginated['posStart'], $bodyXml);
    }

    protected function validatePayload(Request $request): array
    {
        $name = trim((string) $request->input('Name'));
        $productType = $request->input('product_type', 'Kemasan');
        $request->merge(['Name' => $name, 'product_type' => $productType]);
        $isAmpas = $request->boolean('Is_Ampas') || stripos($name, 'ampas') !== false;
        $isProductSpec = $productType === 'Export';

        $validated = $request->validate([
            'Name'         => 'required|string|max:150',
            'product_type' => 'required|in:Kemasan,Export',
            'Volume'       => $isProductSpec ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'Max_Stock'    => ($isAmpas || $isProductSpec) ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'Unit'         => 'nullable|string|max:50',
            'Price'        => 'nullable|numeric|min:0',
        ]);

        return [
            'Name'         => $validated['Name'],
            'product_type' => $validated['product_type'],
            'Volume'       => isset($validated['Volume']) && $validated['Volume'] !== '' ? (float) $validated['Volume'] : 0.0,
            'Is_Ampas'     => $isAmpas,
            'Max_Stock'    => ($isAmpas || $isProductSpec || !isset($validated['Max_Stock']) || $validated['Max_Stock'] === '') ? null : (float) $validated['Max_Stock'],
            'Unit'         => trim((string) ($validated['Unit'] ?? '')),
            'Price'        => isset($validated['Price']) && $validated['Price'] !== '' ? (float) $validated['Price'] : 0.0,
        ];
    }

    public function store(Request $request)
    {
        $payload = $this->validatePayload($request);
        $serialInput = $request->input('serial_numbers');

        DB::transaction(function () use ($payload, $serialInput) {
            $kemasan = CnsMasterKemasan::create($payload);
            $this->syncAvailableSerialNumbers($kemasan, $serialInput);
        });

        return back()->with('success', 'Master kemasan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $kemasan = CnsMasterKemasan::findOrFail($id);
        $payload = $this->validatePayload($request);
        $serialInput = $request->input('serial_numbers');

        DB::transaction(function () use ($kemasan, $payload, $serialInput) {
            $kemasan->update($payload);
            $this->syncAvailableSerialNumbers($kemasan, $serialInput);
        });

        return back()->with('success', 'Master kemasan berhasil diperbarui.');
    }

    protected function syncAvailableSerialNumbers(CnsMasterKemasan $kemasan, $rawSerialNumbers): void
    {
        if (!Schema::hasTable('cns_kemasan_serial_numbers') || $rawSerialNumbers === null) {
            return;
        }

        $parsed = preg_split('/[\r\n,]+/', (string) $rawSerialNumbers);
        $desired = [];
        foreach ($parsed as $sn) {
            $sn = trim($sn);
            if ($sn !== '' && !in_array($sn, $desired, true)) {
                $desired[] = $sn;
            }
        }

        if (!empty($desired)) {
            $conflicts = CnsKemasanSerialNumber::whereIn('serial_number', $desired)
                ->where(function ($q) use ($kemasan) {
                    $q->where('kemasan_id', '!=', $kemasan->Kemasan_ID)
                      ->orWhere('status', '!=', CnsKemasanSerialNumber::STATUS_AVAILABLE);
                })
                ->pluck('serial_number')
                ->all();

            if (!empty($conflicts)) {
                throw ValidationException::withMessages([
                    'serial_numbers' => 'Serial number sudah dipakai atau tidak tersedia: ' . implode(', ', $conflicts),
                ]);
            }
        }

        $existingAvailable = CnsKemasanSerialNumber::where('kemasan_id', $kemasan->Kemasan_ID)
            ->where('status', CnsKemasanSerialNumber::STATUS_AVAILABLE)
            ->pluck('serial_number')
            ->all();

        $toDelete = array_diff($existingAvailable, $desired);
        if (!empty($toDelete)) {
            CnsKemasanSerialNumber::where('kemasan_id', $kemasan->Kemasan_ID)
                ->where('status', CnsKemasanSerialNumber::STATUS_AVAILABLE)
                ->whereIn('serial_number', $toDelete)
                ->delete();
        }

        $toInsert = array_diff($desired, $existingAvailable);
        foreach ($toInsert as $sn) {
            CnsKemasanSerialNumber::create([
                'kemasan_id' => $kemasan->Kemasan_ID,
                'serial_number' => $sn,
                'status' => CnsKemasanSerialNumber::STATUS_AVAILABLE,
            ]);
        }
    }

    public function destroy($id)
    {
        $kemasan = CnsMasterKemasan::findOrFail($id);

        if (Schema::hasTable('cns_inventory_kemasan') && CnsInventoryKemasan::where('packaging_id', $kemasan->Kemasan_ID)->exists()) {
            return back()->with('error', 'Kemasan tidak dapat dihapus karena sudah digunakan di Inventory Kemasan.');
        }

        $kemasan->delete();

        return back()->with('success', 'Master kemasan berhasil dihapus.');
    }
}
