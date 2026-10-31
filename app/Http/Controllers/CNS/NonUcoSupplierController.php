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

    public function indexNonUcoSupplier()
    {
        return view('cns.procurement.non-uco-supplier');
    }

    public function indexNonUcoSupplierXml()
    {
        if (!headers_sent()) {
            header("Content-type: text/xml");
        }

        $rows = DB::table('supplier')
            ->where('Location', Auth::user()->Location)
            ->where('Is_Supplier', 1)
            ->whereIn('Supply_Category', self::CATEGORIES)
            ->orderBy('Company_Name')
            ->get();

        echo "<rows>";
        $nomor = 1;

        foreach ($rows as $row) {
            $emails = $this->emailsOf($row);

            echo '<row id="' . $row->ID . '">';
            echo '<cell>' . $nomor . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row->Supplier_ID) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row->Company_Name) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row->Phone) . '</cell>';
            echo '<cell>' . htmlspecialchars(implode(', ', $emails)) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row->Supply_Category) . '</cell>';
            echo '<cell>' . htmlspecialchars((string) $row->Created_Date) . '</cell>';
            echo '</row>';

            $nomor++;
        }

        echo "</rows>";
    }

    public function newNonUcoSupplier()
    {
        return view('cns.procurement.non-uco-supplier-new', [
            'categories' => self::CATEGORIES,
        ]);
    }

    public function newNonUcoSupplierSubmit(Request $request)
    {
        $data = $this->validateVendor($request);

        $emails = $this->cleanEmails($data['Emails']);

        if (count($emails) < 1) {
            return response()->json(['message' => 'Minimal satu alamat email wajib diisi.'], 422);
        }

        $location = Auth::user()->Location;
        $count    = DB::table('supplier')->where('Location', $location)->count();

        DB::table('supplier')->insert([
            'Location'        => $location,
            'Supplier_ID'     => 'GEU-COMP-2' . sprintf('%04d', $count + 1),
            'Company_Name'    => $data['Company_Name'],
            'Phone'           => $data['Phone'],
            'Email'           => $this->primaryEmail($emails),
            'Emails'          => json_encode($emails),
            'Supply_Category' => $data['Supply_Category'],
            'Is_Company'      => 1,
            'Is_Supplier'     => 1,
            'Is_Consignee'    => 0,
            'Status'          => 'NORMAL',
            'Created_By'      => Auth::user()->username,
            'Created_Date'    => now(),
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
            'emails'     => $this->emailsOf($supplier),
            'categories' => self::CATEGORIES,
        ]);
    }

    public function detailNonUcoSupplierSubmit(Request $request)
    {
        $data = $request->validate([
            'ID'              => 'required|integer|exists:supplier,ID',
            'Company_Name'    => 'required|string|max:100',
            'Phone'           => 'required|string|max:60',
            'Emails'          => 'required|array|min:1|max:10',
            'Emails.*'        => 'required|email|max:150',
            'Supply_Category' => 'required|in:' . implode(',', self::CATEGORIES),
        ]);

        if (!$this->findVendor($data['ID'])) {
            return response()->json(['message' => 'Supplier tidak ditemukan.'], 422);
        }

        $emails = $this->cleanEmails($data['Emails']);

        if (count($emails) < 1) {
            return response()->json(['message' => 'Minimal satu alamat email wajib diisi.'], 422);
        }

        DB::table('supplier')->where('ID', $data['ID'])->update([
            'Company_Name'    => $data['Company_Name'],
            'Phone'           => $data['Phone'],
            'Email'           => $this->primaryEmail($emails),
            'Emails'          => json_encode($emails),
            'Supply_Category' => $data['Supply_Category'],
        ]);

        return 'success';
    }

    private function validateVendor(Request $request): array
    {
        return $request->validate([
            'Company_Name'    => 'required|string|max:100',
            'Phone'           => 'required|string|max:60',
            'Emails'          => 'required|array|min:1|max:10',
            'Emails.*'        => 'required|email|max:150',
            'Supply_Category' => 'required|in:' . implode(',', self::CATEGORIES),
        ]);
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

    private function cleanEmails(array $emails): array
    {
        return array_values(array_filter(array_unique(array_map('trim', $emails)), function ($email) {
            return $email !== '';
        }));
    }

    private function primaryEmail(array $emails)
    {
        return strlen($emails[0]) <= 60 ? $emails[0] : null;
    }
}
