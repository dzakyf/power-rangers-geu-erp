<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AnnexVII;
use Illuminate\Support\Facades\Auth;

class AnnexController extends Controller
{
    public function indexAnnex()
    {
        return view('cns.export.annex-index');
    }

    public function newAnnex()
    {
        return view('cns.export.annex-new');
    }

    public function detailAnnex()
    {
        //
    }

    public function printAnnex(Request $request)
    {
        $annex = AnnexVII::where('id', $request->id)->first();
        return view('cns.export.print.annex-print', compact('annex'));
    }

    public function printAnnexSample()
    {
        return view('cns.export.print.annex-print-sample');
    }
    
    /**
     * Store a new Annex VII record.
     */
    public function store(Request $request)
    {
        // Basic validation (adjust rules as needed)
        $validated = $request->validate([
            'SI_No' => 'nullable|string|max:100',
            'Order_No' => 'nullable|string|max:100',
            'Qty1' => 'nullable|string|max:50',
            'Qty2' => 'nullable|string|max:50',
            'SI_Date' => 'nullable|date',
            'Shipper' => 'nullable|string',
            'Consignee' => 'nullable|string',
            'Product_Name' => 'nullable|string',
            'HS_Code' => 'nullable|string',
            'PEB_Date' => 'nullable|date',
            'declarant_date' => 'nullable|date',
        ]);

        $getRomawi = function($month) {
            $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            return $romawi[(int)$month - 1] ?? '';
        };

        
        $counter = AnnexVII::where('created_date', "LIKE", date("Y"). "%")->count();

        $InvNo = sprintf('%03d',$counter + 1) . "/ANX/" . $getRomawi(date("n")) . "/" . date("Y");
        // prepare data: take only fillable keys from request
        $data = $request->only((new AnnexVII)->getFillable());

        // Add created_by/updated_by if auth available
        if (Auth::check()) {
            $data['created_by'] = Auth::user()->username ?? Auth::id();
            $data['updated_by'] = Auth::user()->username ?? Auth::id();
        }

        $annex = AnnexVII::create($data);

        return response()->json(['success' => true, 'id' => $annex->id, 'message' => 'Annex created']);
    }
}
