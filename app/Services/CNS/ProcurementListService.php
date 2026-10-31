<?php

namespace App\Services\CNS;

use App\BuyerContract;
use App\Http\Controllers\CNS\NonUcoSupplierController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProcurementListService
{
    public function rows(array $filters = []): array
    {
        $location = Auth::user()->Location;

        $contractFilter = $filters['contract'] ?? 'all';
        $groupFilter    = $filters['group'] ?? 'all';
        $productFilter  = $filters['product'] ?? 'all';

        $rows = array_merge(
            $this->contractRows($location),
            $this->supplierRows($location)
        );

        $rows = array_values(array_filter($rows, function ($row) use ($contractFilter, $groupFilter, $productFilter) {
            if ($contractFilter === 'with' && $row['type'] !== 'contract') {
                return false;
            }

            if ($contractFilter === 'without' && $row['type'] !== 'supplier') {
                return false;
            }

            if ($groupFilter !== 'all' && $row['group'] !== $groupFilter) {
                return false;
            }

            if ($productFilter !== 'all' && $row['product'] !== $productFilter) {
                return false;
            }

            return true;
        }));

        usort($rows, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $rows;
    }

    private function contractRows(string $location): array
    {
        $contracts = BuyerContract::where('Location', $location)
            ->orderBy('Created_Date', 'desc')
            ->get();

        $rows = [];

        foreach ($contracts as $contract) {
            $loadingPeriod = '';

            if ($contract->Loading_Start || $contract->Loading_End) {
                $loadingPeriod = trim(($contract->Loading_Start ?? '') . ' - ' . ($contract->Loading_End ?? ''));
            }

            $rows[] = [
                'id'             => 'C-' . $contract->ID,
                'type'           => 'contract',
                'name'           => $contract->Buyer_Name,
                'address'        => $contract->Buyer_Address,
                'phone'          => $contract->Buyer_Phone,
                'email'          => $this->emails($contract->Buyer_Emails, null),
                'group'          => 'UCO',
                'product'        => 'UCO',
                'has_contract'   => true,
                'contract_label' => 'Ya',
                'contract_no'    => $contract->Contract_No,
                'loading_period' => $loadingPeriod,
                'contract_qty'   => $this->number($contract->Quantity_MT),
                'delivered'      => '',
                'outstanding'    => '',
                'status'         => $contract->statusLabel(),
                'file'           => $contract->Contract_File,
            ];
        }

        return $rows;
    }

    private function supplierRows(string $location): array
    {
        $suppliers = DB::table('supplier')
            ->where('Location', $location)
            ->where('Is_Supplier', 1)
            ->whereIn('Supply_Category', NonUcoSupplierController::CATEGORIES)
            ->orderBy('Company_Name')
            ->get();

        $rows = [];

        foreach ($suppliers as $supplier) {
            $rows[] = [
                'id'             => 'S-' . $supplier->ID,
                'type'           => 'supplier',
                'name'           => $supplier->Company_Name,
                'address'        => $supplier->Address ?? '',
                'phone'          => $supplier->Phone ?? '',
                'email'          => $this->emails($supplier->Emails ?? null, $supplier->Email ?? null),
                'group'          => 'Non-UCO',
                'product'        => $supplier->Supply_Category,
                'has_contract'   => false,
                'contract_label' => 'Tidak',
                'contract_no'    => '',
                'loading_period' => '',
                'contract_qty'   => '',
                'delivered'      => '',
                'outstanding'    => '',
                'status'         => '',
                'file'           => '',
            ];
        }

        return $rows;
    }

    private function emails($json, $fallback): string
    {
        $emails = [];

        if (!empty($json)) {
            $decoded = json_decode($json, true);

            if (is_array($decoded)) {
                $emails = array_values(array_filter(array_map('trim', $decoded)));
            }
        }

        if (count($emails) === 0 && !empty($fallback)) {
            $emails = [$fallback];
        }

        return implode(', ', $emails);
    }

    private function number($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format((float) $value, 3, '.', ',');
    }
}
