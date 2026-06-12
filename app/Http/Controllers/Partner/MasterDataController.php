<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\MasterPackaging;
use App\MasterCategory;
use SimpleXMLElement;

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

    public function indexWarehouseFacility()
    {
        return view('partner.warehouse.facility.index');
    }

    public function createWarehouseFacility()
    {
        return view('partner.warehouse.facility.create');
    }

    public function getWarehouseFacility(Request $request)
    {
        $facilityType = $request->input('hub_type'); // mobil | booth
        $category     = $request->input('type');          // inventory | access | support
        $format       = $request->input('format');        // json | (default xml)

        $allowedFacilityTypes = ['mobil', 'booth'];
        if (!in_array($facilityType, $allowedFacilityTypes)) {
            return response()->json(['error' => 'Invalid facility_type. Must be one of: ' . implode(', ', $allowedFacilityTypes)], 422);
        }

        $allowedCategories = ['inventory', 'access', 'support'];
        if (!in_array($category, $allowedCategories)) {
            return response()->json(['error' => 'Invalid type. Must be one of: ' . implode(', ', $allowedCategories)], 422);
        }

        $facilities = DB::table('master_facility')
            ->where('type', $facilityType)
            ->where('category', $category)
            ->where('is_deleted', 0)
            ->get();

        $rows = [];

        foreach ($facilities as $index => $facility) {
            $children = json_decode($facility->children);

            if (is_array($children)) {
                $children = implode(', ', $children);
            } elseif (is_object($children)) {
                $parts = [];
                foreach ($children as $k => $v) {
                    $parts[] = "$k : " . (is_array($v) ? implode(', ', $v) : json_encode($v));
                }
                $children = implode(', ', $parts);
            }

            $entry = [
                'no'          => $index + 1,
                'description' => $facility->description,
                'children'    => (string) $children,
            ];

            if ($category === 'inventory') {
                $entry['asset_type'] = $facility->asset_type;
            }

            $rows[] = array_merge(['id' => $facility->id], $entry);
        }

        if ($format === 'json') {
            return response()->json(['data' => $rows]);
        }

        $xml = new \SimpleXMLElement('<rows/>');
        foreach ($rows as $row) {
            $node = $xml->addChild('row');
            $node->addAttribute('id', $row['id']);
            foreach ($row as $key => $value) {
                if ($key === 'id') {
                    continue;
                }
                $node->addChild('cell', htmlspecialchars((string) $value));
            }
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'text/xml');
    }

    public function submitWarehouseFacility(Request $request)
    {
        DB::transaction(function () use ($request) {
            DB::table('master_facility')->insert([
                'type'              => $request->input('type'),
                'category'          => $request->input('category'),
                'sub_category'      => $request->input('category'),
                'description'       => $request->input('description'),
                'children'          => json_encode($request->input('children', [])),
                'asset_type'        => $request->input('asset_type') ?? '',
                'depreciation_year' => $request->input('depreciation_year'),
            ]);
        });

        return response()->json(['status' => 'success']);
    }

    public function deleteWarehouseFacility(Request $r)
    {
        $id = $r->input('id');

        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID is required'], 400);
        }

        $deleted = DB::transaction(function () use ($id) {
            return DB::table('master_facility')->where('id', $id)->where('is_deleted', 0)->update(['is_deleted' => 1]);
        });

        if (!$deleted) {
            return response()->json(['status' => 'error', 'message' => 'Record not found'], 404);
        }

        return response()->json(['status' => 'success']);
    }


    public function getMasterFleet(Request $r)
    {
        $rows = $this->fetchMasterFleet($r->query('id'));

        if ($r->query('format') === 'xml') {
            return $this->masterFleetAsXml($rows);
        }

        return $this->masterFleetAsJson($rows);
    }

    private function fetchMasterFleet(?string $id)
    {
        $query = DB::table('master_fleet')->select('*');

        if ($id) {
            $query->where('id', $id);
        }

        return $query->get();
    }

    private function masterFleetAsJson(\Illuminate\Support\Collection $rows)
    {
        return response()->json(['data' => $rows]);
    }

    private function masterFleetAsXml(\Illuminate\Support\Collection $rows)
    {
        $xml     = new \SimpleXMLElement('<rows/>');
        $counter = 1;

        foreach ($rows as $row) {
            $item = $xml->addChild('row');
            $item->addAttribute('ID', $row->ID);

            $item->addChild('cell', $counter);
            $item->addChild('cell', htmlspecialchars((string) $row->ID));
            $item->addChild('cell', htmlspecialchars((string) $row->Police_Number));
            $item->addChild('cell', htmlspecialchars((string) $row->Warehouse_Id));
            $item->addChild('cell', htmlspecialchars((string) $row->Fleet_Brand));
            $item->addChild('cell', htmlspecialchars((string) $row->Description));
            $item->addChild('cell', htmlspecialchars((string) $row->Status));
            $item->addChild('cell', htmlspecialchars((string) $row->Created_At));

            $counter++;
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function submitCreateMasterFleet(Request $r)
    {
        try {
            DB::transaction(function () use ($r) {
                DB::table('master_fleet')->insert([
                    'Police_Number' => $r->input('Police_Number'),
                    'Warehouse_Id'  => $r->input('Warehouse_Id'),
                    'Fleet_Brand'   => $r->input('Fleet_Brand'),
                    'Description'   => $r->input('Description'),
                    'Status'        => $r->input('Status'),
                    'Created_At'    => now(),
                ]);
            });

            return response()->json(['status' => 'success', 'message' => 'Fleet berhasil ditambahkan.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function indexMasterFleet()
    {
        return view('partner.fleet.index');
    }

    public function createMasterFleet()
    {
        return view('partner.fleet.create');
    }

    public function getAllUsers(Request $r)
    {
        $search = $r->query('q', '');

        $query = DB::table('users')->select('ID', 'Name')->orderBy('id', 'asc');

        if ($search !== '') {
            $query->where('Name', 'like', '%' . $search . '%');
        }

        return response()->json(['data' => $query->get()]);
    }
    // === Master Packaging ===
    public function packagingData(Request $r)
    {
        $data = MasterPackaging::all();

        if ($r->query('format') === 'xml') {
            $xml = new SimpleXMLElement('<rows/>');
            foreach ($data as $i => $d) {
                $row = $xml->addChild('row');
                $row->addChild('cell', $i + 1);
                $row->addChild('cell', $d->Name);
                $row->addChild('cell', $d->Capacity ?? '');
                $row->addChild('cell', $d->Unit);
                $row->addChild('cell', $d->Description ?? '');
                $row->addChild('cell', $d->Status);
            }
            return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
        }

        return response()->json(['data' => $data]);
    }

    public function packagingSubmit(Request $r)
    {
        $model = MasterPackaging::create($r->only(['Name', 'Capacity', 'Unit', 'Description', 'Status']));
        return response()->json(['success' => true, 'id' => $model->ID]);
    }

    public function packagingEdit(Request $r)
    {
        MasterPackaging::where('ID', $r->id)->update($r->only(['Name', 'Capacity', 'Unit', 'Description', 'Status']));
        return response()->json(['success' => true]);
    }

    public function packagingDelete(Request $r)
    {
        MasterPackaging::where('ID', $r->id)->delete();
        return response()->json(['success' => true]);
    }

    // === Master Category ===
    public function categoryData(Request $r)
    {
        $data = MasterCategory::all();

        if ($r->query('format') === 'xml') {
            $xml = new SimpleXMLElement('<rows/>');
            foreach ($data as $i => $d) {
                $row = $xml->addChild('row');
                $row->addChild('cell', $i + 1);
                $row->addChild('cell', $d->Name);
                $row->addChild('cell', $d->Description ?? '');
                $row->addChild('cell', $d->Status);
            }
            return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
        }

        return response()->json(['data' => $data]);
    }

    public function categorySubmit(Request $r)
    {
        $model = MasterCategory::create($r->only(['Name', 'Description', 'Status']));
        return response()->json(['success' => true, 'id' => $model->ID]);
    }

    public function categoryEdit(Request $r)
    {
        MasterCategory::where('ID', $r->id)->update($r->only(['Name', 'Description', 'Status']));
        return response()->json(['success' => true]);
    }

    public function categoryDelete(Request $r)
    {
        MasterCategory::where('ID', $r->id)->delete();
        return response()->json(['success' => true]);
    }
}
