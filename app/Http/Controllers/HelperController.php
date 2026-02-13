<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelperController extends Controller
{

    public function provinceSearch(Request $request)
    {
        $search = $request->input('q');
        $province = \DB::table('master_kodepos')
            ->select("Province")
            ->groupBy("Province")
            ->orderBy("Province", "asc")
            ->limit(8);
        if ($search != '') {
            $province->where("Province", "like", "%".$search."%");
        }
        $province = $province->get();

        return response()->json($province);
    }

    public function citySearch(Request $request, $province)
    {
        if ($province == '') {
            return response()->json([]);
        }
        $province = base64_decode($province);
        $search = $request->input('q');
        $city = \DB::table('master_kodepos')
            ->select("City")
            ->groupBy("City")
            ->orderBy("City", "asc")
            ->where("Province", $province)
            ->limit(8);
        if ($search != '') {
            $city->where("City", "like", "%".$search."%");
        }
        $city = $city->get();

        return response()->json($city);
    }
}
