<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ListsController extends Controller
{
    public function index()
    {
        return view('partner.partner-business-lists');
    }

    public function indexXML()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        // Sample data, replace with actual database query
        $partners = DB::table('opportunity_partner')->get();
        foreach ($partners as $key => $partner) {
            echo "<row id=\"$partner->id\" >";
                echo "<cell>".($key + 1)."</cell>";
                echo "<cell>".htmlspecialchars($partner->register_as)."</cell>";
                if ($partner->register_as == 'individual') {
                    echo "<cell>".htmlspecialchars($partner->full_name)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->address)."</cell>";
                }else {
                    echo "<cell>".htmlspecialchars($partner->company_official_name)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->head_office_address)."</cell>";
                }
                echo "<cell>".htmlspecialchars($partner->company_warehouse_count)."</cell>";
                echo "<cell>".htmlspecialchars($partner->company_experience)."</cell>";
                if ($partner->register_as == 'individual') {
                    echo "<cell>".htmlspecialchars($partner->q1)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->q2)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->q3)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->q4)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->q5)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->q6)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->package_option)."</cell>";
                }else {
                    echo "<cell>".htmlspecialchars($partner->c_q1)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->c_q2)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->c_q3)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->c_q4)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->c_q5)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->c_q6)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->company_package)."</cell>";
                }

                if ($partner->status == 'ACTIVE') {
                    echo "<cell>Blast</cell>";
                }else {
                    echo "<cell></cell>";
                }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function detail(Request $request)
    {
        // Fetch the partner record from the opportunity_partner table by id
        $partner = DB::table('opportunity_partner')->where('id', $request->ID)->first();

        // Provide PIC list used by the form (if users table exists)
        $pic = [];
        if (Schema::hasTable('users')) {
            $pic = DB::table('users')->select('username', DB::raw("COALESCE(name, username) as Name"))->get();
        }

        return view('partner.business-lists-detail', compact('partner', 'pic'));
    }
}
