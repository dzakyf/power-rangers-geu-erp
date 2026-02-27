<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $province->where("Province", "like", "%" . $search . "%");
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
            $city->where("City", "like", "%" . $search . "%");
        }
        $city = $city->get();

        return response()->json($city);
    }

    public function districtSearch(Request $r)
    {
        $q = $r->query();
        $province = $q['province'];
        $city = $q['city'];

        $res = DB::table('master_kodepos as mk')->select('*');

        if ($province) {
            $res->where('mk.Province', 'LIKE', '%' . $province . '%');
        }

        if ($city) {
            $res->where('mk.City', 'LIKE', '%' . $city . '%');
        }

        $res = $res->limit(8)->get();

        return response()->json(['data' => $res], 200);
    }

    public function subdistrictSearch(Request $r)
    {
        $q = $r->query();
        $province = $q['province'];
        $city = $q['city'];
        $district = $q['district'];

        $res = DB::table('master_kodepos as mk')->select('*');

        if ($province) {
            $res->where('mk.Province', 'LIKE', '%' . $province . '%');
        }

        if ($city) {
            $res->where('mk.City', 'LIKE', '%' . $city . '%');
        }
        if ($district) {
            $res->where('mk.District', 'LIKE', '%' . $district . '%');
        }

        $res = $res->limit(8)->get();

        return response()->json(['data' => $res], 200);
    }

    public function postalCodeSearch(Request $r)
    {
        $q = $r->query();
        $province = $q['province'];
        $city = $q['city'];
        $district = $q['district'];
        $subdistrict = $q['subdistrict'];

        $res = DB::table('master_kodepos as mk')->select('*');

        if ($province) {
            $res->where('mk.Province', 'LIKE', '%' . $province . '%');
        }

        if ($city) {
            $res->where('mk.City', 'LIKE', '%' . $city . '%');
        }

        if ($district) {
            $res->where('mk.District', 'LIKE', '%' . $district . '%');
        }

        if ($subdistrict) {
            $res->where('mk.Sub_District', 'LIKE', '%' . $subdistrict . '%');
        }

        $res = $res->limit(8)->get();

        return response()->json(['data' => $res], 200);
    }
}
