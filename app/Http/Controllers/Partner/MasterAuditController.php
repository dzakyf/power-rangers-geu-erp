<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterAuditCategory;
use App\User;
use Illuminate\Http\Request;

class MasterAuditController extends Controller
{
    public function indexMasterAuditCategory()
    {
        return view('partner.master-audit-category');
    }

    public function indexMasterAuditCategoryXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
        }
        $nomor = 1;

        $get = MasterAuditCategory::orderBy('created_date', 'desc')->get();
        
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->category) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->category_description) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->parameter_list) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->parameter_score) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->status) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }
}
