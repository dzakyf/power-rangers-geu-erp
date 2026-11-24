<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\Support\DhtmlxGridPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MasterCompanyController extends Controller
{
    public function indexMasterCompany()
    {
        return view('cns.master.company');
    }


    public function getMasterCompanyCollection(Request $request)
    {
        return DB::table("supplier")
            ->where("Location", auth()->user()->Location)
            ->when($request->type === 'CONSIGNEE', fn($q) => $q->where('Is_Consignee', 1))
            ->when($request->type === 'SUPPLIER', fn($q) => $q->where('Is_Supplier', 1))
            ->orderBy('Company_Name', 'ASC')
            ->get();
    }

    public function getSupplierCompany(Request $r)
    {
        return  view('cns.master.supplier');
    }

    public function getBuyerCompany(Request $r)
    {
        return  view('cns.master.buyer');
    }

    public function indexMasterCompanyXml(Request $request)
    {
        $query = DB::table("supplier")
            ->where("Location", auth()->user()->Location)
            ->when($request->type === 'CONSIGNEE', fn($q) => $q->where('Is_Consignee', 1))
            ->when($request->type === 'SUPPLIER', fn($q) => $q->where('Is_Supplier', 1));

        if (!$request->filled('sort_col')) {
            $query->orderBy('Company_Name', 'ASC');
        }

        $paginated = DhtmlxGridPagination::paginateQuery($query, $request, self::supplierGridPaginationOptions());
        $bodyXml = self::buildSupplierRowsXml($paginated['rows'], $paginated['posStart']);

        return DhtmlxGridPagination::response($paginated['total'], $paginated['posStart'], $bodyXml);
    }

    public static function supplierGridPaginationOptions(): array
    {
        $boolFilter = function (string $col) {
            return function ($q, string $term) use ($col) {
                $lower = strtolower($term);
                if (str_contains('yes', $lower) || $lower === '1') {
                    $q->where($col, 1);
                } elseif (str_contains('no', $lower) || $lower === '0') {
                    $q->where($col, 0);
                } else {
                    $q->whereRaw('1 = 0');
                }
            };
        };

        $presenceFilter = function (string $col) {
            return function ($q, string $term) use ($col) {
                $lower = strtolower($term);
                if (str_starts_with($lower, 'tidak')) {
                    $q->whereNull($col);
                } elseif (str_contains('ada', $lower)) {
                    $q->whereNotNull($col);
                }
            };
        };

        return [
            'default_page_size' => 25,
            'col_map' => [
                1  => 'Supplier_ID',
                2  => 'Company_Name',
                3  => 'Is_Consignee',
                4  => 'Is_Supplier',
                5  => 'Contact_Person',
                6  => 'City',
                7  => 'Phone',
                8  => 'Fax',
                9  => 'Email',
                10 => 'Sales_Name',
                11 => 'Sales_No',
                12 => 'Payment',
                13 => 'Address',
                14 => 'Contact_Person',
                15 => 'Letterhead',
                16 => 'Footer',
                17 => 'Logo',
                18 => 'Logo_Inverse',
                19 => 'Stamps',
            ],
            'filter_map' => [
                1  => 'Supplier_ID',
                2  => 'Company_Name',
                3  => $boolFilter('Is_Consignee'),
                4  => $boolFilter('Is_Supplier'),
                5  => 'Contact_Person',
                6  => 'City',
                7  => 'Phone',
                8  => 'Fax',
                9  => 'Email',
                10 => 'Sales_Name',
                11 => 'Sales_No',
                12 => 'Payment',
                13 => 'Address',
                14 => 'Contact_Person',
                15 => $presenceFilter('Letterhead'),
                16 => $presenceFilter('Footer'),
                17 => $presenceFilter('Logo'),
                18 => $presenceFilter('Logo_Inverse'),
                19 => $presenceFilter('Stamps'),
            ],
        ];
    }

    public static function buildSupplierRowsXml($rows, int $posStart = 0): string
    {
        $nomor = $posStart + 1;
        $bodyXml = '';

        foreach ($rows as $dapat) {
            $bodyXml .= '<row id="' . htmlspecialchars((string) $dapat->ID) . '">';
            $bodyXml .= '<cell>' . $nomor++ . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $dapat->Supplier_ID) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $dapat->Company_Name) . '</cell>';
            $bodyXml .= '<cell>' . ($dapat->Is_Consignee ? 'Yes' : 'No') . '</cell>';
            $bodyXml .= '<cell>' . ($dapat->Is_Supplier ? 'Yes' : 'No') . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $dapat->Contact_Person) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $dapat->City) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $dapat->Phone) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $dapat->Fax) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $dapat->Email) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $dapat->Sales_Name) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) ($dapat->Sales_No ?? '')) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $dapat->Payment) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $dapat->Address) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $dapat->Contact_Person) . '</cell>';
            $bodyXml .= '<cell>' . ($dapat->Letterhead === null ? 'Tidak ada' : 'Ada') . '</cell>';
            $bodyXml .= '<cell>' . ($dapat->Footer === null ? 'Tidak ada' : 'Ada') . '</cell>';
            $bodyXml .= '<cell>' . ($dapat->Logo === null ? 'Tidak ada' : 'Ada') . '</cell>';
            $bodyXml .= '<cell>' . ($dapat->Logo_Inverse === null ? 'Tidak ada' : 'Ada') . '</cell>';
            $bodyXml .= '<cell>' . ($dapat->Stamps === null ? 'Tidak ada' : 'Ada') . '</cell>';
            $bodyXml .= '</row>';
        }

        return $bodyXml;
    }

    public function newMasterCompany()
    {
        return view('cns.master.company-new');
    }

    public function newMasterCompanySubmit(Request $request)
    {

        $emails = $this->companyEmails($request);

        $param = $request->param;
        $param = explode('&', $param);
        $param = array_filter($param, fn($p) => !str_starts_with($p, 'filter_type='));
        $params = [];
        foreach ($param as $key => $value) {
            $value = explode('=', $value);
            $params[$value[0]] = $value[1];
        }
        $ars = "";
        $ax = "";
        $isi = "";
        $arr = [];
        foreach ($params as $key => $value) {

            if ($key != 'ID' && $key != 'UpdateData') {
                $ars .= "$key = '$value',";
                $ax .= "$key,";
                $isi .= "'$value',";
                $arr[$key] = $value;
            }
        }

        $arr['Is_Company']   = $request->input('Is_Company',   1);
        $arr['Is_Supplier']  = $request->input('Is_Supplier',  0);
        $arr['Is_Consignee'] = $request->input('Is_Consignee', 0);

        $Saya = Auth::User()->username;
        $LSP = Auth::user()->Location;

        $ars = substr($ars, 0, -1);
        $ax = substr($ax, 0, -1);
        $isi = substr($isi, 0, -1);
        $cekakun = DB::table('supplier')->where('Location', $LSP)->orderBy('ID', 'desc')->count();
        $Supplier_ID = "GEU-COMP-" . ($arr['Is_Consignee'] ? '2' : '1') . sprintf("%04d", $cekakun + 1);

        if ($request->LetterHead != 'undefined') {
            $file = $request->LetterHead;
            $filename = "LetterHead_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['LetterHead'] = "filesupplier/" . $filename;
        }

        if ($request->Footer != 'undefined') {
            $file = $request->Footer;
            $filename = "Footer_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Footer'] = "filesupplier/" . $filename;
        }

        if ($request->Logo != 'undefined') {
            $file = $request->Logo;
            $filename = "Logo_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Logo'] = "filesupplier/" . $filename;
        }

        if ($request->Logo_Inverse != 'undefined') {
            $file = $request->Logo_Inverse;
            $filename = "LogoInverse_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Logo_Inverse'] = "filesupplier/" . $filename;
        }

        if ($request->Stamps != 'undefined') {
            $file = $request->Stamps;
            $filename = "Stamps_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Stamps'] = "filesupplier/" . $filename;
        }

        $this->applyCompanyEmails($arr, $emails);

        $arr['Supplier_ID'] = $Supplier_ID;
        $arr['Status'] = "NORMAL";
        $arr['Created_By'] = $Saya;
        $arr['Location'] = $LSP;

        DB::table('supplier')->insert($arr);

        // DB::insert(DB::raw("INSERT INTO supplier($ax,Status,Supplier_ID,Created_By,Location) VALUES($isi,'NORMAL','$Supplier_ID','$Saya','$LSP')"));
        return "success";
    }

    public function detailMasterCompany(Request $request)
    {
        $company = DB::table("supplier")->where("ID", $request->ID)->first();
        return view('cns.master.company-detail', compact('company'));
    }

    public function detailMasterCompanySubmit(Request $request)
    {

        $emails = $this->companyEmails($request);

        $param = $request->param;
        $param = explode('&', $param);
        $param = array_filter($param, fn($p) => !str_starts_with($p, 'filter_type='));
        $params = [];
        foreach ($param as $key => $value) {
            $value = explode('=', $value);
            $params[$value[0]] = $value[1];
        }
        $ars = "";
        $ax = "";
        $isi = "";
        $arr = [];
        foreach ($params as $key => $value) {

            if ($key != 'ID' && $key != 'UpdateData' && $key != 'LetterHead' && $key != 'Footer' && $key != 'Logo' && $key != 'Logo_Inverse') {
                $ars .= "$key = '$value',";
                $ax .= "$key,";
                $isi .= "'$value',";
                $arr[$key] = $value;
            }
        }
        $Saya = Auth::User()->username;
        $LSP = Auth::user()->Location;

        if ($request->LetterHead != 'undefined') {
            $file = $request->LetterHead;
            $filename = "LetterHead_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Letterhead'] = "filesupplier/" . $filename;
        }

        if ($request->Footer != 'undefined') {
            $file = $request->Footer;
            $filename = "Footer_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Footer'] = "filesupplier/" . $filename;
        }

        if ($request->Logo != 'undefined') {
            $file = $request->Logo;
            $filename = "Logo_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Logo'] = "filesupplier/" . $filename;
        }

        if ($request->Logo_Inverse != 'undefined') {
            $file = $request->Logo_Inverse;
            $filename = "LogoInverse_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Logo_Inverse'] = "filesupplier/" . $filename;
        }

        if ($request->Stamps != 'undefined') {
            $file = $request->Stamps;
            $filename = "Stamps_" . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('filesupplier'), $filename);
            $arr['Stamps'] = "filesupplier/" . $filename;
        }

        $arr['Is_Company']   = $request->input('Is_Company',  1);
        $arr['Is_Supplier']  = $request->input('Is_Supplier',  0);
        $arr['Is_Consignee'] = $request->input('Is_Consignee', 0);

        $this->applyCompanyEmails($arr, $emails);

        DB::table('supplier')->where('ID', $params['ID'])->update($arr);

        // DB::insert(DB::raw("INSERT INTO supplier($ax,Status,Supplier_ID,Created_By,Location) VALUES($isi,'NORMAL','$Supplier_ID','$Saya','$LSP')"));

        return "success";
    }


    private function companyEmails(Request $request): array
    {
        $data = $request->validate([
            'Emails'   => 'required|array|min:1|max:10',
            'Emails.*' => 'required|email|max:150',
        ]);

        return array_values(array_unique(array_map('trim', $data['Emails'])));
    }

    private function applyCompanyEmails(array &$arr, array $emails): void
    {
        $primary = $emails[0] ?? null;

        if ($primary !== null && strlen($primary) > 60) {
            $primary = null;
        }

        $arr['Email'] = $primary;
        $arr['Emails'] = count($emails) > 0 ? json_encode($emails) : null;
    }

    private function importColumns(): array
    {
        return [
            'Company_Name',
            'Is_Consignee',
            'Is_Supplier',
            'Contact_Person',
            'NPWP',
            'City',
            'Street',
            'Address',
            'Phone',
            'Fax',
            'HP',
            'Email',
            'Sales_Name',
            'Sales_Phone',
            'Payment',
            'Note',
            'Bank_Rupiah',
            'Bank_Rupiah_Account',
            'Bank_Rupiah_Name',
            'Bank_Foreign',
            'Bank_Foreign_Account',
            'Bank_Foreign_Name',
            'Bank_Foreign_Currency',
        ];
    }

    public function downloadImportTemplate()
    {
        $columns = $this->importColumns();
        $sample = [
            'PT Contoh Makmur',
            'Yes',
            'No',
            'Budi Santoso',
            '01.234.567.8-901.000',
            'Jakarta',
            'Jl. Sudirman No. 1',
            'Jl. Sudirman No. 1, Jakarta',
            '021-5550100',
            '021-5550101',
            '08123456789',
            'budi@contoh.co.id',
            'Andi',
            '08129876543',
            'TOP 30 Days',
            'Sample row',
            'BCA',
            '1234567890',
            'PT Contoh Makmur',
            '',
            '',
            '',
            '',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($columns, null, 'A1');
        $sheet->fromArray($sample, null, 'A2');
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'master-company-import-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importMasterCompany(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        if (count($rows) < 2) {
            return response()->json(['message' => 'File Excel kosong atau tidak memiliki baris data.'], 422);
        }

        $headerRow = array_shift($rows);
        $colMap = [];
        foreach ($headerRow as $idx => $header) {
            $key = strtolower(trim((string) $header));
            if ($key !== '') {
                $colMap[$key] = $idx;
            }
        }

        if (!isset($colMap['company_name']) || !isset($colMap['email'])) {
            return response()->json(['message' => 'Header wajib Company_Name dan Email tidak ditemukan.'], 422);
        }

        $user = Auth::user();
        $location = $user->Location;

        $existingNames = DB::table('supplier')
            ->where('Location', $location)
            ->pluck('Company_Name')
            ->map(fn($n) => strtolower(trim((string) $n)))
            ->filter()
            ->flip()
            ->all();

        $seq = DB::table('supplier')->where('Location', $location)->count();

        $imported = 0;
        $skipped = [];
        $errors = [];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;
            $hasData = count(array_filter($row, fn($v) => trim((string) $v) !== '')) > 0;
            if (!$hasData) {
                continue;
            }

            $get = fn(string $col) => isset($colMap[strtolower($col)])
                ? trim((string) ($row[$colMap[strtolower($col)]] ?? ''))
                : '';

            $companyName = $get('Company_Name');
            if ($companyName === '') {
                $errors[] = ['row' => $rowNum, 'company_name' => '', 'reason' => 'Company_Name wajib diisi.'];
                continue;
            }

            $normName = strtolower($companyName);
            if (isset($existingNames[$normName])) {
                $skipped[] = ['row' => $rowNum, 'company_name' => $companyName, 'reason' => 'Company_Name sudah terdaftar.'];
                continue;
            }

            $emails = $this->parseImportEmails($get('Email'));
            if (is_string($emails)) {
                $errors[] = ['row' => $rowNum, 'company_name' => $companyName, 'reason' => $emails];
                continue;
            }

            $isConsignee = $this->parseImportBool($get('Is_Consignee'));
            $isSupplier = $this->parseImportBool($get('Is_Supplier'));
            $seq++;

            $arr = [
                'Company_Name'          => $companyName,
                'Is_Company'            => 1,
                'Is_Consignee'          => $isConsignee,
                'Is_Supplier'           => $isSupplier,
                'Contact_Person'        => $get('Contact_Person'),
                'NPWP'                  => $get('NPWP'),
                'City'                  => $get('City'),
                'Street'                => $get('Street'),
                'Address'               => $get('Address'),
                'Phone'                 => $get('Phone'),
                'Fax'                   => $get('Fax'),
                'HP'                    => $get('HP'),
                'Sales_Name'            => $get('Sales_Name'),
                'Sales_Phone'           => $get('Sales_Phone'),
                'Payment'               => $get('Payment'),
                'Note'                  => $get('Note'),
                'Bank_Rupiah'           => $get('Bank_Rupiah'),
                'Bank_Rupiah_Account'   => $get('Bank_Rupiah_Account'),
                'Bank_Rupiah_Name'      => $get('Bank_Rupiah_Name'),
                'Bank_Foreign'          => $get('Bank_Foreign'),
                'Bank_Foreign_Account'  => $get('Bank_Foreign_Account'),
                'Bank_Foreign_Name'     => $get('Bank_Foreign_Name'),
                'Bank_Foreign_Currency' => $get('Bank_Foreign_Currency'),
                'Supplier_ID'           => 'GEU-COMP-' . ($isConsignee ? '2' : '1') . sprintf('%04d', $seq),
                'Status'                => 'NORMAL',
                'Created_By'            => $user->username,
                'Location'              => $location,
            ];

            $this->applyCompanyEmails($arr, $emails);
            DB::table('supplier')->insert($arr);

            $existingNames[$normName] = true;
            $imported++;
        }

        return response()->json([
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ]);
    }

    /** @return array<int, string>|string */
    private function parseImportEmails(string $raw)
    {
        $parts = preg_split('/[;,]+/', $raw) ?: [];
        $emails = array_values(array_unique(array_filter(array_map('trim', $parts))));

        if (count($emails) === 0) {
            return 'Minimal 1 email wajib diisi.';
        }
        if (count($emails) > 10) {
            return 'Maksimal 10 email per company.';
        }
        foreach ($emails as $email) {
            if (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return "Format email tidak valid: {$email}";
            }
        }

        return $emails;
    }

    private function parseImportBool(string $val): int
    {
        return in_array(strtolower(trim($val)), ['1', 'yes', 'ya', 'true'], true) ? 1 : 0;
    }

}
