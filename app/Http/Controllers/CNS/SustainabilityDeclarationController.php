<?php

namespace App\Http\Controllers\CNS;

use App\SustainabilityDeclaration;
use App\Http\Controllers\Controller;
use App\Services\CNS\CnsCodeGenerator;
use App\Support\DhtmlxGridPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SustainabilityDeclarationController extends Controller
{
    public function indexSustainabilityDeclaration()
    {
        return view('cns.export.sd');
    }

    public function indexSustainabilityDeclarationXml(Request $request)
    {
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        $companies = [];
        foreach ($company as $value) {
            $companies[$value->Supplier_ID] = $value->Company_Name;
        }

        $query = SustainabilityDeclaration::query();

        $colMap = [
            1 => 'SD_No',
            2 => 'Issue_Date',
            4 => 'Contract_No',
            5 => 'Dispatch_Date',
            6 => 'Quantity',
            7 => 'Country_Of_Origin',
            8 => 'Status',
        ];

        $paginated = DhtmlxGridPagination::paginateQuery($query, $request, [
            'col_map' => $colMap,
            'default_page_size' => 25,
        ]);

        $bodyXml = '';
        $nomor = $paginated['posStart'] + 1;

        foreach ($paginated['rows'] as $value) {
            $consigneeName = $companies[$value->Consignee] ?? $value->Consignee;
            $bodyXml .= "<row id=\"$value->ID\">";
            $bodyXml .= "<cell>$nomor</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->SD_No)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Issue_Date)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($consigneeName)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Contract_No)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Dispatch_Date)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Quantity)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Country_Of_Origin)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Status)."</cell>";
            $bodyXml .= "</row>\n";

            $nomor++;
        }

        return DhtmlxGridPagination::response($paginated['total'], $paginated['posStart'], $bodyXml);
    }

    public function printSustainabilityDeclaration(Request $request)
    {
        $company = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        foreach ($company as $value) {
            $companies[$value->Supplier_ID] = $value;
        }
        $sd = SustainabilityDeclaration::where('SD_No', $request->Task)->first();
        return view('cns.export.print.sd', compact('sd', 'companies'));
    }

    public function newSustainabilityDeclaration()
    {
        $companies = DB::table('supplier')->where("Location", auth()->user()->Location)->get();
        $SdNo      = CnsCodeGenerator::nextSdNo();

        return view('cns.export.sd-new', compact('companies', 'SdNo'));
    }

    public function newSustainabilityDeclarationSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token'], $r['approved_by'], $r['approved_at']);

        $r['ISCC_Compliant']         = isset($r['ISCC_Compliant']) ? 1 : 0;
        $r['EU_RED_Compliant']       = isset($r['EU_RED_Compliant']) ? 1 : 0;
        $r['Dispatch_Same_As_Supplier']   = isset($r['Dispatch_Same_As_Supplier']) ? 1 : 0;
        $r['Receipt_Same_As_Recipient']   = isset($r['Receipt_Same_As_Recipient']) ? 1 : 0;
        $r['GHG_DDV_Oil_Extraction'] = isset($r['GHG_DDV_Oil_Extraction']) ? 1 : 0;
        $r['GHG_DDV_Soil_N2O']       = isset($r['GHG_DDV_Soil_N2O']) ? 1 : 0;
        $r['Addon_FSS']              = isset($r['Addon_FSS']) ? 1 : 0;
        $r['Addon_EUDR']             = isset($r['Addon_EUDR']) ? 1 : 0;
        $r['Addon_NonGMO_Feed']      = isset($r['Addon_NonGMO_Feed']) ? 1 : 0;
        $r['Addon_NonGMO_Technical'] = isset($r['Addon_NonGMO_Technical']) ? 1 : 0;

        $r['Created_By']   = auth()->user()->username;
        $r['Created_Date'] = now();
        $r['Status']       = 'CREATED';

        DB::transaction(function () use ($r) {
            $r['SD_No'] = CnsCodeGenerator::nextSdNo($r['Issue_Date'] ?? null, true);
            SustainabilityDeclaration::create($r);
        });

        echo "success";
    }

    public function detailSustainabilityDeclaration(Request $request)
    {
        $companies = $this->locationSuppliers();
        $sd = $this->findSd($request);
        if (!$sd) {
            abort(404, 'Sustainability Declaration tidak ditemukan.');
        }

        return view('cns.export.sd-detail', compact('companies', 'sd'));
    }

    public function detailSustainabilityDeclarationSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token'], $r['Status'], $r['approved_by'], $r['approved_at']);

        $sd = $this->findSd($request);
        if ($sd && $sd->Status === 'APPROVED') {
            return response('Sustainability Declaration sudah di-approve dan tidak dapat diubah');
        }

        $r['ISCC_Compliant']              = isset($r['ISCC_Compliant']) ? 1 : 0;
        $r['EU_RED_Compliant']            = isset($r['EU_RED_Compliant']) ? 1 : 0;
        $r['Dispatch_Same_As_Supplier']   = isset($r['Dispatch_Same_As_Supplier']) ? 1 : 0;
        $r['Receipt_Same_As_Recipient']   = isset($r['Receipt_Same_As_Recipient']) ? 1 : 0;
        $r['GHG_DDV_Oil_Extraction']      = isset($r['GHG_DDV_Oil_Extraction']) ? 1 : 0;
        $r['GHG_DDV_Soil_N2O']            = isset($r['GHG_DDV_Soil_N2O']) ? 1 : 0;
        $r['Addon_FSS']                   = isset($r['Addon_FSS']) ? 1 : 0;
        $r['Addon_EUDR']                  = isset($r['Addon_EUDR']) ? 1 : 0;
        $r['Addon_NonGMO_Feed']           = isset($r['Addon_NonGMO_Feed']) ? 1 : 0;
        $r['Addon_NonGMO_Technical']      = isset($r['Addon_NonGMO_Technical']) ? 1 : 0;

        SustainabilityDeclaration::where('SD_No', $r['SD_No'])->update($r);

        echo "success";
    }

    public function approve(Request $request)
    {
        $user = auth()->user();
        $isAllowed = $user && (
            $user->username === 'admin'
            || in_array($user->Priv_ID ?? '', ['ADM', 'CNS', 'SPV', 'MANAGER'])
            || (method_exists($user, 'hasRole') && ($user->hasRole('ADM') || $user->hasRole('super-admin')))
            || (method_exists($user, 'can') && (
                $user->can('menu.SI00314.approve')
                || $user->can('cns.sustainability_declaration.approve')
                || $user->can('cns.sustainability_declaration.edit')
            ))
        );

        if (!$isAllowed) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses approve Sustainability Declaration.'], 403);
        }

        $sd = $this->findSd($request);
        if (!$sd) {
            return response()->json(['status' => 'error', 'message' => 'Sustainability Declaration tidak ditemukan.'], 404);
        }

        if ($sd->Status === 'APPROVED') {
            return response()->json(['status' => 'error', 'message' => 'Sustainability Declaration sudah berstatus APPROVED.'], 422);
        }

        $sd->Status = 'APPROVED';
        $sd->approved_by = $user->id;
        $sd->approved_at = now();
        $this->saveSd($sd);

        return response()->json([
            'status'  => 'success',
            'message' => "Sustainability Declaration {$sd->SD_No} berhasil di-approve.",
        ]);
    }

    protected function locationSuppliers()
    {
        return DB::table('supplier')->where('Location', auth()->user()->Location)->get();
    }

    protected function findSd(Request $request): ?SustainabilityDeclaration
    {
        $task = trim((string) ($request->input('sd_no') ?: $request->input('SD_No') ?: $request->input('id') ?: $request->input('Task', '')));
        if ($task === '') {
            return null;
        }

        $sd = SustainabilityDeclaration::where('SD_No', $task)->first();
        if (!$sd && is_numeric($task)) {
            $sd = SustainabilityDeclaration::find($task);
        }

        return $sd;
    }

    protected function saveSd(SustainabilityDeclaration $sd): void
    {
        $sd->save();
    }
}
