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


        return response()->json(['data' => $query->get()]);
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

        DB::table('opportunity')->where('ID', $id)->update([
            'Full_Name'          => $r->input('Full_Name'),
            'Phone_Number'       => $r->input('Phone_Number'),
            'Hub_Type'           => $r->input('Hub_Type'),
            'Capital_Estimation' => $r->input('Capital_Estimation'),
            'Source_Channel'     => $r->input('Source_Channel'),
            'Response'           => $r->input('Response'),
            'Qualification'      => $r->input('Qualification'),
            'Classification'     => $r->input('Classification'),
            'Status'             => $r->input('Status'),
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
