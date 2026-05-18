<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDataController extends Controller
{
    public function getLeadQualification()
    {
        $master = DB::table('master_leads_qualification')->get();

        return response()->json(['data' => $master]);
    }

    public function getLeadClassification()
    {
        $master = DB::table('master_leads_classification')->get();

        return response()->json(['data' => $master]);
    }

    public function getLeadStatus()
    {
        $master = DB::table('master_leads_status')->get();

        return response()->json(['data' => $master]);
    }

    public function getLeadResponse()
    {
        $master = DB::table('master_leads_response')->get();

        return response()->json(['data' => $master]);
    }

    public function getLeadCancelReason()
    {
        $master = DB::table('master_leads_reason')->get();

        return response()->json(['data' => $master]);
    }

    public function POOPageIndex()
    {
        return view('partner.poo.index');
    }

    public function createPOOPage()
    {
        return view('partner.poo.create');
    }

    public function updatePOOPage()
    {
        return view('partner.poo.edit');
    }

    // POO : Point of Origin
    public function createPOO(Request $r)
    {
        DB::beginTransaction();

        try {
            DB::table('master_poo')->insert([
                'Type'                => $r->input('Type') || "SUPPLIER",
                'Name'                => $r->input('Name'),
                'PIC'                 => $r->input('PIC'),
                'Code'                => $r->input('Code'),
                'Employee'            => $r->input('Employee'),
                'Phone'               => $r->input('Phone'),
                'Position'            => $r->input('Position'),
                'Category'            => $r->input('Category'),
                'UCO_Type'            => $r->input('UCO_Type'),
                'Price'               => $r->input('Price'),
                'Price_Unit'          => $r->input('Price_Unit'),
                'Province'            => $r->input('Province'),
                'City'                => $r->input('City'),
                'Subdistrict'         => $r->input('Subdistrict'),
                'Village'             => $r->input('Village'),
                'Cycle'               => $r->input('Cycle'),
                'Full_Address'        => $r->input('Full_Address'),
                'Location_Coordinate' => $r->input('Location_Coordinate'),
                'Nearest_Warehouse'   => $r->input('Nearest_Warehouse'),
                'Bank_Account_Number' => $r->input('Bank_Account_Number'),
                'Bank_Account_Name'   => $r->input('Bank_Account_Name'),
                'Bank_Name'           => $r->input('Bank_Name'),
                'Point'               => $r->input('Point'),
            ]);

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function updatePOO(Request $r)
    {
        $id = $r->ID;

        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID is required'], 400);
        }

        DB::beginTransaction();

        try {
            $affected = DB::table('master_poo')->where('ID', $id)->update([
                'Type'                => $r->input('Type'),
                'Name'                => $r->input('Name'),
                'PIC'                 => $r->input('PIC'),
                'Code'                => $r->input('Code'),
                'Employee'            => $r->input('Employee'),
                'Phone'               => $r->input('Phone'),
                'Position'            => $r->input('Position'),
                'Category'            => $r->input('Category'),
                'UCO_Type'            => $r->input('UCO_Type'),
                'Price'               => $r->input('Price'),
                'Price_Unit'          => $r->input('Price_Unit'),
                'Province'            => $r->input('Province'),
                'City'                => $r->input('City'),
                'Subdistrict'         => $r->input('Subdistrict'),
                'Village'             => $r->input('Village'),
                'Cycle'               => $r->input('Cycle'),
                'Full_Address'        => $r->input('Full_Address'),
                'Location_Coordinate' => $r->input('Location_Coordinate'),
                'Nearest_Warehouse'   => $r->input('Nearest_Warehouse'),
                'Bank_Account_Number' => $r->input('Bank_Account_Number'),
                'Bank_Account_Name'   => $r->input('Bank_Account_Name'),
                'Bank_Name'           => $r->input('Bank_Name'),
                'Point'               => $r->input('Point'),
            ]);

            if (!$affected) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Record not found'], 404);
            }

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function queryPOO(Request $r)
    {
        $id = $r->query('id');

        $query = DB::table('master_poo');

        if ($id) {
            $query->where('ID', $id);
        }

        return $query->get();
    }

    public function getPOO(Request $r)
    {
        $rows = $this->queryPOO($r);
        $type = $r->query('format');

        if ($type === 'xml') {
            $xml = new \SimpleXMLElement('<rows/>');

            foreach ($rows as $row) {
                $item = $xml->addChild('row');
                $item->addAttribute('ID', $row->ID);

                foreach ((array) $row as $key => $value) {
                    $item->addChild('cell', htmlspecialchars((string) $value));
                }
            }

            return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
        }

        return response()->json(['data' => $rows]);
    }

    public function provinceSearch(Request $request)
    {
        $search = strtolower($request->input('q', ''));
        $province = DB::table('master_kodepos')
            ->select("ID", "Province")
            ->groupBy("Province")
            ->orderBy("Province", "asc");

        if ($search !== '') {
            $province->whereRaw("LOWER(Province) LIKE ?", ["%" . $search . "%"]);
        }

        return response()->json(['data' => $province->get()]);
    }

    public function citySearch(Request $request)
    {
        $search   = strtolower($request->input('q', ''));
        $province = strtolower($request->query('province', ''));

        $city = DB::table('master_kodepos')
            ->select("ID", "Province", "City")
            ->groupBy("City")
            ->orderBy("City", "asc")
            ->whereRaw("LOWER(Province) = ?", [$province]);

        if ($search !== '') {
            $city->whereRaw("LOWER(City) LIKE ?", ["%" . $search . "%"]);
        }

        return response()->json(['data' => $city->get()]);
    }

    public function districtSearch(Request $r)
    {
        $province = strtolower($r->query('province', ''));
        $city     = strtolower($r->query('city', ''));

        $res = DB::table('master_kodepos as mk')->select('ID', 'Province', 'City', 'District')->groupBy('District');

        if ($province) {
            $res->whereRaw("LOWER(mk.Province) LIKE ?", ["%" . $province . "%"]);
        }
        if ($city) {
            $res->whereRaw("LOWER(mk.City) LIKE ?", ["%" . $city . "%"]);
        }

        return response()->json(['data' => $res->get()], 200);
    }

    public function subdistrictSearch(Request $r)
    {
        $province = strtolower($r->query('province', ''));
        $city     = strtolower($r->query('city', ''));
        $district = strtolower($r->query('district', ''));

        $res = DB::table('master_kodepos as mk')->select('ID', 'Province', 'City', 'District', 'Sub_District')->groupBy('Sub_District');

        if ($province) {
            $res->whereRaw("LOWER(mk.Province) LIKE ?", ["%" . $province . "%"]);
        }
        if ($city) {
            $res->whereRaw("LOWER(mk.City) LIKE ?", ["%" . $city . "%"]);
        }
        if ($district) {
            $res->whereRaw("LOWER(mk.District) LIKE ?", ["%" . $district . "%"]);
        }

        return response()->json(['data' => $res->get()], 200);
    }

    public function postalCodeSearch(Request $r)
    {
        $province    = strtolower($r->query('province', ''));
        $city        = strtolower($r->query('city', ''));
        $district    = strtolower($r->query('district', ''));
        $subdistrict = strtolower($r->query('subdistrict', ''));

        $res = DB::table('master_kodepos as mk')->select('ID', 'Province', 'City', 'District', 'Sub_District', 'ZipCode')->groupBy('ZipCode');

        if ($province) {
            $res->whereRaw("LOWER(mk.Province) LIKE ?", ["%" . $province . "%"]);
        }
        if ($city) {
            $res->whereRaw("LOWER(mk.City) LIKE ?", ["%" . $city . "%"]);
        }
        if ($district) {
            $res->whereRaw("LOWER(mk.District) LIKE ?", ["%" . $district . "%"]);
        }
        if ($subdistrict) {
            $res->whereRaw("LOWER(mk.Sub_District) LIKE ?", ["%" . $subdistrict . "%"]);
        }

        return response()->json(['data' => $res->get()], 200);
    }
}
