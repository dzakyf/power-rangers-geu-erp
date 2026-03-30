<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UploadService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class ComplianceController extends Controller
{
    protected $month;
    protected $year;
    protected $userService;
    protected $uploadService;

    public function __construct(UserService $u, UploadService $up)
    {
        $this->userService = $u;
        $this->year = Carbon::now()->year;
        $this->month = Carbon::now()->format('m');
        $this->uploadService = $up;
    }

    public function indexTax()
    {
        return view('partner.compliance.tax-index');
    }

    public function indexLegal()
    {
        return view('partner.compliance.legal-index');
    }

    public function createLegalCompliance()
    {
        return view('partner.compliance.legal-create');
    }


    public function selectTaxComplianceData(Request $r)
    {

        $q = $r->query();

        if (isset($q['tax_id'])) {
            return $this->getTaxComplianceDataById($r);
        }

        return $this->getTaxComplianceData($r);
    }

    public function getTaxComplianceData(Request $r)
    {

        $q = $r->query();

        $d = DB::table('config_tax as ct')->select('ct.*')
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $count = 1;
        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('Tax_Id', $d->ID);
            $row->addChild('cell', $count);
            $row->addChild('cell', $d->Type);
            $row->addChild('cell', $d->Object);
            $row->addChild('cell', $d->Percentile);
            $row->addChild('cell', $d->Base_Calculation);
            $row->addChild('cell', $d->In_Charge);
            $row->addChild('cell', $d->Description);
            $row->addChild('cell', $d->Hub_Type);
            $row->addChild('cell', $d->Min_Percentile);
            $row->addChild('cell', $d->Max_Percentile);
            $count++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getTaxComplianceDataById(Request $r)
    {

        $q = $r->query();

        $d = DB::table('config_tax as ct')->select('ct.*')
            ->where('ct.ID', '=', $q['tax_id'])
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $count = 1;
        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('Tax_Id', $d->ID);
            $row->addChild('cell', $count);
            $row->addChild('cell', $d->Type);
            $row->addChild('cell', $d->Object);
            $row->addChild('cell', $d->Percentile);
            $row->addChild('cell', $d->Base_Calculation);
            $row->addChild('cell', $d->In_Charge);
            $row->addChild('cell', $d->Description);
            $row->addChild('cell', $d->Min_Percentile);
            $row->addChild('cell', $d->Max_Percentile);
            $count++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getLegalComplianceData(Request $r)
    {
        $d = DB::table('config_legal as cl')->select('cl.*')
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');


        $count = 1;
        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->ID);
            $row->addChild('cell', $count);
            $row->addChild('cell', $d->Document_Name);
            $row->addChild('cell', $d->Description);
            $row->addChild('cell', 'Lihat Dokumen^ ' . $d->Media_Url); // display full link and open it
            $row->addChild('cell', $d->Created_At);

            $count++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function updateTaxCompliance(Request $r)
    {
        $b = $r->all();
        $id = DB::table('config_tax as ct')->where('ct.ID', '=', $b['tax_id'])->update([
            "Type" => $b["Type"],
            "Object" => $b["Object"],
            "Percentile" => $b["Percentile"],
            "Base_Calculation" => $b["Base_Calculation"],
            "In_Charge" => $b["In_Charge"],
            "Description" => $b["Description"],
            "Min_Percentile" => $b["Min_Percentile"],
            "Max_Percentile" => $b["Max_Percentile"],
        ]);

        return response()->json(['message' => 'success']);
    }

    public function inputLegalCompliance(Request $r)
    {
        $b = $r->all();

        $files = $r->file('legal_doc'); // now expects legal_doc[] array

        try {
            $id = DB::table('config_legal')->insertGetId([
                'Document_Name' => $b['Document_Name'],
                'Description'   => $b['Description'],
                'Media_Url'     => $b['Media_Url'],
            ]);

            if (!empty($files)) {
                foreach ($files as $file) {
                    $path     = $file->store("$this->year/$this->month", 'public');
                    $ext      = $file->getClientOriginalExtension();
                    $filename = basename($path);
                    $desc     = "Legal compliance document";
                    $topic    = "legal-compliance";
                    $mapTo    = $id;

                    $this->uploadService->uploadMetaFile($path, $filename, $desc, $ext, $topic, $mapTo);
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Data berhasil disimpan'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
