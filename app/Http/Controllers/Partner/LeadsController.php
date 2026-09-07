<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadsController extends Controller
{
    public function getLeads(Request $r)
    {
        if ($r->query('type') === 'registered') {
            return $this->getRegisteredLeads($r);
        }

        $id = $r->query('id');

        $query = DB::table('opportunity as o')->select('o.*');

        if ($id) {
            $query->where('o.ID', $id);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function getRegisteredLeads(Request $r)
    {
        $leadId = $r->query('lead_id');
        $rowId = $r->query('id'); //row id 

        $query = DB::table('opportunity_partner as op')
            ->select(
                'op.*'
            );

        if ($leadId) {
            $query->where('op.lead_id', $leadId);
        }

        if ($rowId) {
            $query->where('op.id',  $rowId);
        }

        if ($rowId && $leadId) {
            $query->where('op.id',  $rowId)->where('op.lead_id', $leadId);
        }

        $query = $query->get();
        foreach ($query as $key => $value) {
            $query[$key]->all_documents = DB::table('opportunity_partner_file')->where("opportunity_id", $value->id)->get()->pluck('file_path', 'type')->toArray();
        }


        return response()->json(['data' => $query]);
    }

    public function updateLeads(Request $r)
    {
        $id = $r->query('id');

        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID is required'], 400);
        }

        $exists = DB::table('opportunity')->where('ID', $id)->exists();

        if (!$exists) {
            return response()->json(['status' => 'error', 'message' => 'Record not found'], 404);
        }
        
        $array = [
            '<50 Juta' => 1,
            '< 50 Juta' => 1,
            '100 - 200 Juta' => 2,
            '200 - 300 Juta' => 3,
            '>300 Juta' => 4,
            '> 300 Juta' => 4,
        ];

        // Keep the legacy int flag in sync with Hub_Type (1 = Mobil, 2 = Booth)
        // so leads lists never fall back to "Opsi 0".
        DB::table('opportunity')->where('ID', $id)->update([
            'Full_Name'           => $r->input('Full_Name'),
            'Phone_Number'        => $r->input('Phone_Number'),
            'KTP'                 => $r->input('KTP'),
            'NPWP'                => $r->input('NPWP'),
            'Hub_Type'            => $r->input('Hub_Type'),
            'Partnership_Interest' => $r->input('Hub_Type') === 'booth' ? 2 : 1,
            'Capital_Estimation'  => $array[$r->input('Capital_Estimation')] ?? 0,
            'Source_Channel'      => $r->input('Source_Channel'),
            'Response'            => $r->input('Response'),
            'Qualification'       => $r->input('Qualification'),
            'Classification'      => $r->input('Classification'),
            'Status'              => $r->input('Status'),
            'Reason'              => $r->input('Reason'),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function deleteLeads(Request $r)
    {
        $id = $r->query('id');

        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID is required'], 400);
        }

        $deleted = DB::table('opportunity')->where('ID', $id)->delete();

        if (!$deleted) {
            return response()->json(['status' => 'error', 'message' => 'Record not found'], 404);
        }

        return response()->json(['status' => 'success']);
    }
}
