<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NonUcoSupplierController extends Controller
{
    public const CATEGORIES = [
        'Flexibag',
        'Besi Penyangga',
        'Kemasan dan Ampas',
    ];

    public function vendors()
    {
        $rows = DB::table('supplier')
            ->where('Location', Auth::user()->Location)
            ->where('Is_Supplier', 1)
            ->orderBy('Company_Name')
            ->get();

        return response()->json($rows->map(function ($row) {
            return [
                'id'              => (int) $row->ID,
                'name'            => $row->Company_Name,
                'address'         => $row->Address,
                'phone'           => $row->Phone,
                'emails'          => $this->emailsOf($row),
                'supply_category' => $row->Supply_Category,
            ];
        })->values());
    }

    public function newNonUcoSupplierSubmit(Request $request)
    {
        $data = $request->validate([
            'Vendor_ID'       => 'required|integer|exists:supplier,ID',
            'Supply_Category' => 'required|in:' . implode(',', self::CATEGORIES),
        ]);

        $vendor = DB::table('supplier')
            ->where('ID', $data['Vendor_ID'])
            ->where('Location', Auth::user()->Location)
            ->where('Is_Supplier', 1)
            ->first();

        if (!$vendor) {
            return response()->json(['message' => 'Vendor tidak ditemukan.'], 422);
        }

        DB::table('supplier')->where('ID', $vendor->ID)->update([
            'Supply_Category' => $data['Supply_Category'],
        ]);

        return 'success';
    }

    public function detailNonUcoSupplier(Request $request)
    {
        $supplier = $this->findVendor($request->ID);

        if (!$supplier) {
            abort(404);
        }

        return view('cns.procurement.non-uco-supplier-detail', [
            'supplier'   => $supplier,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function detailNonUcoSupplierSubmit(Request $request)
    {
        $data = $request->validate([
            'ID'              => 'required|integer|exists:supplier,ID',
            'Supply_Category' => 'required|in:' . implode(',', self::CATEGORIES),
        ]);

        if (!$this->findVendor($data['ID'])) {
            return response()->json(['message' => 'Supplier tidak ditemukan.'], 422);
        }

        DB::table('supplier')->where('ID', $data['ID'])->update([
            'Supply_Category' => $data['Supply_Category'],
        ]);

        return 'success';
    }

    private function findVendor($id)
    {
        return DB::table('supplier')
            ->where('ID', $id)
            ->where('Location', Auth::user()->Location)
            ->where('Is_Supplier', 1)
            ->whereIn('Supply_Category', self::CATEGORIES)
            ->first();
    }

    private function emailsOf($row): array
    {
        $emails = [];

        if (!empty($row->Emails)) {
            $decoded = json_decode($row->Emails, true);
            if (is_array($decoded)) {
                $emails = array_values(array_filter($decoded));
            }
        }

        if (count($emails) === 0 && !empty($row->Email)) {
            $emails = [$row->Email];
        }

        return $emails;
    }
}
