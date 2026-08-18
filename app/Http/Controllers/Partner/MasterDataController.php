<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\MasterPackaging;
use App\MasterCategory;
use SimpleXMLElement;
use App\MasterWorkOrderStatus;

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
        $master = DB::table('master_leads_status')->where('status', '!=', 'Negotiation')->get();

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
        $validated = $r->validate([
            'Type'                => 'required|string|max:255',
            'Name'                => 'required|string|max:255',
            'PIC'                 => 'required|string|max:255',
            'Employee'            => 'required|string|max:255',
            'Phone'               => 'required|string|max:255',
            'Position'            => 'required|string|max:255',
            'Category'            => 'required|string|max:255',
            'UCO_Type'            => 'required|string|max:255',
            'Price'               => 'required|numeric|min:0',
            'Price_Unit'          => 'required|string|max:255',
            'Province'            => 'required|string|max:255',
            'City'                => 'required|string|max:255',
            'Subdistrict'         => 'required|string|max:255',
            'Village'             => 'required|string|max:255',
            'Full_Address'        => 'required|string|max:500',
            'Location_Coordinate' => ['required', 'string', 'max:255', function ($attr, $val, $fail) {
            if (!preg_match('/^\-?\d+(?:\.\d+)?,\-?\d+(?:\.\d+)?$/', $val)) {
                $fail('Format Location_Coordinate harus "lat,lng" (contoh: -6.2,106.8)');
                return;
            }
            [$lat, $lng] = array_map('trim', explode(',', $val));
            if ((float)$lat < -90 || (float)$lat > 90) {
                $fail('Latitude harus antara -90 dan 90');
            }
            if ((float)$lng < -180 || (float)$lng > 180) {
                $fail('Longitude harus antara -180 dan 180');
            }
        }],
            'Nearest_Warehouse'   => 'required',
            'Bank_Account_Number' => 'required|string|max:255',
            'Bank_Account_Name'   => 'required|string|max:255',
            'Bank_Name'           => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $highest = DB::table('master_poo')->max('ID') ?? 0;
            $code = app(\App\Services\GeneratorService::class)
                ->generateApplicationCode($highest, 'POO');

            DB::table('master_poo')->insert([
                'Type'                => $validated['Type'] ?? "SUPPLIER",
                'Name'                => $validated['Name'],
                'PIC'                 => $validated['PIC'],
                'Code'                => $code,
                'Employee'            => $validated['Employee'],
                'Phone'               => $validated['Phone'],
                'Position'            => $validated['Position'],
                'Category'            => $validated['Category'],
                'UCO_Type'            => $validated['UCO_Type'],
                'Price'               => $validated['Price'],
                'Price_Unit'          => $validated['Price_Unit'],
                'Province'            => $validated['Province'],
                'City'                => $validated['City'],
                'Subdistrict'         => $validated['Subdistrict'],
                'Village'             => $validated['Village'],
                'Cycle'               => $r->input('Cycle'),
                'Full_Address'        => $validated['Full_Address'],
                'Location_Coordinate' => $validated['Location_Coordinate'],
                'Lat'               => $this->parseLat($validated['Location_Coordinate']),
                'Lng'               => $this->parseLng($validated['Location_Coordinate']),
                'Nearest_Warehouse'   => $validated['Nearest_Warehouse'],
                'Bank_Account_Number' => $validated['Bank_Account_Number'],
                'Bank_Account_Name'   => $validated['Bank_Account_Name'],
                'Bank_Name'           => $validated['Bank_Name'],
                'Point'               => $r->input('Point'),
                'Updated_At'          => now(),
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
        $validated = $r->validate([
            'ID'                  => 'required|integer|exists:master_poo,ID',
            'Type'                => 'required|string|max:255',
            'Name'                => 'required|string|max:255',
            'PIC'                 => 'required|string|max:255',
            'Employee'            => 'required|string|max:255',
            'Phone'               => 'required|string|max:255',
            'Position'            => 'required|string|max:255',
            'Category'            => 'required|string|max:255',
            'UCO_Type'            => 'required|string|max:255',
            'Price'               => 'required|numeric|min:0',
            'Price_Unit'          => 'required|string|max:255',
            'Province'            => 'required|string|max:255',
            'City'                => 'required|string|max:255',
            'Subdistrict'         => 'required|string|max:255',
            'Village'             => 'required|string|max:255',
            'Full_Address'        => 'required|string|max:500',
            'Location_Coordinate' => ['required', 'string', 'max:255', function ($attr, $val, $fail) {
            if (!preg_match('/^\-?\d+(?:\.\d+)?,\-?\d+(?:\.\d+)?$/', $val)) {
                $fail('Format Location_Coordinate harus "lat,lng" (contoh: -6.2,106.8)');
                return;
            }
            [$lat, $lng] = array_map('trim', explode(',', $val));
            if ((float)$lat < -90 || (float)$lat > 90) {
                $fail('Latitude harus antara -90 dan 90');
            }
            if ((float)$lng < -180 || (float)$lng > 180) {
                $fail('Longitude harus antara -180 dan 180');
            }
        }],
            'Nearest_Warehouse'   => 'required',
            'Bank_Account_Number' => 'required|string|max:255',
            'Bank_Account_Name'   => 'required|string|max:255',
            'Bank_Name'           => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $affected = DB::table('master_poo')->where('ID', $validated['ID'])->update([
                'Type'                => $validated['Type'],
                'Name'                => $validated['Name'],
                'PIC'                 => $validated['PIC'],
                'Code'                => $r->input('Code'),
                'Employee'            => $validated['Employee'],
                'Phone'               => $validated['Phone'],
                'Position'            => $validated['Position'],
                'Category'            => $validated['Category'],
                'UCO_Type'            => $validated['UCO_Type'],
                'Price'               => $validated['Price'],
                'Price_Unit'          => $validated['Price_Unit'],
                'Province'            => $validated['Province'],
                'City'                => $validated['City'],
                'Subdistrict'         => $validated['Subdistrict'],
                'Village'             => $validated['Village'],
                'Cycle'               => $r->input('Cycle'),
                'Full_Address'        => $validated['Full_Address'],
                'Location_Coordinate' => $validated['Location_Coordinate'],
                'Lat'               => $this->parseLat($validated['Location_Coordinate']),
                'Lng'               => $this->parseLng($validated['Location_Coordinate']),
                'Nearest_Warehouse'   => $validated['Nearest_Warehouse'],
                'Bank_Account_Number' => $validated['Bank_Account_Number'],
                'Bank_Account_Name'   => $validated['Bank_Account_Name'],
                'Bank_Name'           => $validated['Bank_Name'],
                'Point'               => $r->input('Point'),
            ]);

            $record = DB::table("master_poo")->where("ID", $validated['ID'])->first();
            if (!$record) {
                DB::rollBack();
                return response()->json(["status" => "error", "message" => "Record not found"], 404);
            }
            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    public function deletePOO(Request $r)
    {
        $id = $r->id;
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID is required'], 400);
        }

        $exists = DB::table('master_poo')->where('ID', $id)->first();
        if (!$exists) {
            return response()->json(['success' => false, 'message' => 'Record not found'], 404);
        }

        DB::table('master_poo')->where('ID', $id)->update(['Is_Active' => 0]);
        return response()->json(['success' => true, 'message' => 'Data berhasil dinonaktifkan']);
    }


    public function getBanks(Request $r)
    {
        $data = DB::table('master_bank')
            ->where('Is_Active', true)
            ->select('ID as id', 'Bank_Name as text')
            ->orderBy('Bank_Name')
            ->get();

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function getMasterWarehouses(Request $r)
    {
        $data = DB::table('master_warehouse')
            ->select('ID as id', DB::raw("CONCAT(Name, ' (', Type, ')') as text"))
            ->orderBy('Name')
            ->get();

        return response()->json(['status' => 'success', 'data' => $data]);
    }
    private function queryPOO(Request $r)
    {
        $id = $r->query('id');

        $query = DB::table('master_poo as m')->where('m.Is_Active', 1)
            ->leftJoin('users as u', 'm.Created_By', '=', 'u.id')
            ->select('m.*', 'u.Name as Created_By_Name');

        if ($id) {
            $query->where('m.ID', $id);
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

        if ($request->input('id')) {
            $facility = DB::table('master_facility')
                ->where('id', $request->input('id'))
                ->where('is_deleted', 0)
                ->first();
            return response()->json(['data' => $facility ? [$facility] : []]);
        }

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
                'no'               => $index + 1,
                'description'      => $facility->description,
                'children'         => (string) $children,
                'depreciation_year'=> $facility->depreciation_year,
                'shelf_life'       => $facility->shelf_life,
                'asset_type'       => $facility->asset_type,
                'price'            => $facility->price,
            ];

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
                'shelf_life'        => $request->input('shelf_life'),
                'price'             => $request->input('price'),
            ]);
        });

        return response()->json(['status' => 'success']);
    }

    public function editWarehouseFacility(Request $request)
    {
        $id = $request->input('id');
        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID is required'], 400);
        }

        $updated = DB::transaction(function () use ($request, $id) {
            return DB::table('master_facility')->where('id', $id)->where('is_deleted', 0)->update([
                'description'       => $request->input('description'),
                'children'          => json_encode($request->input('children', [])),
                'asset_type'        => $request->input('asset_type') ?? '',
                'depreciation_year' => $request->input('depreciation_year'),
                'shelf_life'        => $request->input('shelf_life'),
                'price'             => $request->input('price'),
            ]);
        });

        if (!$updated) {
            return response()->json(['status' => 'error', 'message' => 'Record not found'], 404);
        }

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
        $query = DB::table('master_fleet as mf')
            ->leftJoin('warehouse_partner as wp', 'mf.Warehouse_Id', '=', 'wp.id')
            ->select('mf.*', 'wp.warehouse_location as Warehouse_Name');

        if ($id) {
            $query->where('mf.id', $id);
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
            $item->addChild('cell', htmlspecialchars((string) ($row->Warehouse_Name ?? $row->Warehouse_Id)));
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
    public function packagingIndex()
    {
        return view('partner.packaging.index');
    }
    public function packagingData(Request $r)
    {
        $query = MasterPackaging::query();

        if ($r->query('id')) {
            $query->where('ID', $r->query('id'));
        }

        $data = $query->get();

        if ($r->query('format') === 'xml') {
            $xml = new SimpleXMLElement('<rows/>');
            foreach ($data as $i => $d) {
                $row = $xml->addChild('row');
                $row->addAttribute('ID', $d->ID);
                $row->addChild('cell', $i + 1);
                $row->addChild('cell', htmlspecialchars($d->Name));
                $row->addChild('cell', htmlspecialchars($d->Capacity ?? ''));
                $row->addChild('cell', htmlspecialchars($d->Unit));
                $row->addChild('cell', htmlspecialchars($d->Description ?? ''));
                $row->addChild('cell', htmlspecialchars($d->Status));
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
    public function categoryIndex()
    {
        return view('partner.category.index');
    }

    public function categoryData(Request $r)
    {
        $query = MasterCategory::query();

        if ($r->query('id')) {
            $query->where('ID', $r->query('id'));
        }

        $data = $query->get();

        if ($r->query('format') === 'xml') {
            $xml = new SimpleXMLElement('<rows/>');
            foreach ($data as $i => $d) {
                $row = $xml->addChild('row');
                $row->addAttribute('ID', $d->ID);
                $row->addChild('cell', $i + 1);
                $row->addChild('cell', htmlspecialchars($d->Name));
                $row->addChild('cell', htmlspecialchars($d->Description ?? ''));
                $row->addChild('cell', htmlspecialchars($d->Status));
            }
            return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
        }

        return response()->json(['data' => $data]);
    }

    public function categorySubmit(Request $r)
    {
        $name = trim($r->input('Name'));
        if (!$name) {
            return response()->json(['success' => false, 'message' => 'Name is required'], 400);
        }

        $existing = MasterCategory::where('Name', $name)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Kategori "' . $name . '" sudah ada'], 400);
        }

        $model = MasterCategory::create($r->only(['Name', 'Description', 'Status']));
        return response()->json(['success' => true, 'id' => $model->ID]);
    }
    public function categoryEdit(Request $r)
    {
        $name = trim($r->input('Name'));
        if ($name) {
            $duplicate = MasterCategory::where('Name', $name)->where('ID', '!=', $r->id)->first();
            if ($duplicate) {
                return response()->json(['success' => false, 'message' => 'Kategori "' . $name . '" sudah ada'], 400);
            }
        }

        MasterCategory::where('ID', $r->id)->update($r->only(['Name', 'Description', 'Status']));
        return response()->json(['success' => true]);
    }
    public function categoryDelete(Request $r)
    {
        MasterCategory::where('ID', $r->id)->delete();
        return response()->json(['success' => true]);
    }
    public function packagingCreate()
    {
        return view('partner.packaging.create');
    }

    public function categoryCreate()
    {
        return view('partner.category.create');
    }

    public function workOrderStatusIndex()
    {
        return view('partner.work-order-status.index');
    }

    public function workOrderStatusCreate()
    {
        return view('partner.work-order-status.create');
    }

    public function workOrderStatusData(Request $r)
    {
        $query = MasterWorkOrderStatus::query();

        if ($r->query('id')) {
            $query->where('ID', $r->query('id'));
        }

        $data = $query->get();

        if ($r->query('format') === 'xml') {
            $xml = new SimpleXMLElement('<rows/>');
            foreach ($data as $i => $d) {
                $row = $xml->addChild('row');
                $row->addAttribute('ID', $d->ID);
                $row->addChild('cell', $i + 1);
                $row->addChild('cell', htmlspecialchars($d->Code));
                $row->addChild('cell', htmlspecialchars($d->Name));
                $row->addChild('cell', htmlspecialchars($d->Description ?? ''));
                $row->addChild('cell', htmlspecialchars($d->Is_Active));
            }
            return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
        }

        return response()->json(['data' => $data]);
    }

    public function workOrderStatusSubmit(Request $r)
    {
        $model = MasterWorkOrderStatus::create($r->only(['Name', 'Code', 'Description', 'Is_Active']));
        return response()->json(['success' => true, 'id' => $model->ID]);
    }

    public function workOrderStatusEdit(Request $r)
    {
        MasterWorkOrderStatus::where('ID', $r->id)->update($r->only(['Name', 'Code', 'Description', 'Is_Active']));
        return response()->json(['success' => true]);
    }

    public function workOrderStatusDelete(Request $r)
    {
        MasterWorkOrderStatus::where('ID', $r->id)->delete();
        return response()->json(['success' => true]);
    }

    public function containerIndex()
    {
        return view('partner.container.index');
    }

    public function containerCreate()
    {
        return view('partner.container.create');
    }

    public function containerData(Request $r)
    {
        $query = DB::table('master_container as mc')
            ->leftJoin('warehouse_partner as wp', 'mc.Warehouse_ID', '=', 'wp.id')
            ->select('mc.*', 'wp.warehouse_location as Warehouse_Name');

        if ($r->query('id')) {
            $query->where('mc.ID', $r->query('id'));
        }

        if ($r->query('warehouse_id')) {
            $query->where('mc.Warehouse_ID', $r->query('warehouse_id'));
        }

        $data = $query->get();

        if ($r->query('format') === 'xml') {
            $xml = new SimpleXMLElement('<rows/>');
            foreach ($data as $i => $d) {
                $row = $xml->addChild('row');
                $row->addAttribute('ID', $d->ID);
                $row->addChild('cell', $i + 1);
                $row->addChild('cell', htmlspecialchars($d->Name));
                $row->addChild('cell', htmlspecialchars(number_format($d->Volume ?? 0, 2, ',', '.')));
                $row->addChild('cell', htmlspecialchars($d->Note ?? ''));
                $row->addChild('cell', htmlspecialchars($d->Warehouse_Name ?? ''));
                $row->addChild('cell', htmlspecialchars($d->Status));
            }
            return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
        }

        return response()->json(['data' => $data]);
    }

    public function containerSubmit(Request $r)
    {
        $array = $r->only(['Name', 'Volume', 'Note', 'Status', 'Warehouse_ID']);
        $array['Created_By'] = auth()->user()->username ?? 'system';
        $array['Created_At'] = now();
        $model = DB::table('master_container')->insertGetId($array);
        return response()->json(['success' => true, 'ID' => $model]);
    }

    public function containerEdit(Request $r)
    {
        DB::table('master_container')->where('ID', $r->id)->update($r->only(['Name', 'Volume', 'Note', 'Status', 'Warehouse_ID']));
        return response()->json(['success' => true]);
    }

    public function containerDelete(Request $r)
    {
        DB::table('master_container')->where('ID', $r->id)->delete();
        return response()->json(['success' => true]);
    }

    public function getContainer(Request $r)
    {
        $data = DB::table('master_container')
            ->select('ID as id', 'Name', 'Volume', 'Warehouse_ID', 'Note', 'Status', 'Created_By', 'Created_At')
            ->where('Status', "active")
            ->when($r->id, fn($q) => $q->where('ID', $r->id))
            ->get();

        return response()->json(['status' => 'success', 'data' => $data]);
    }


    public function configSystemIndex()
    {
        return view('credential.settings.index');
    }

    public function configSystemData(Request $r)
    {
        $data = DB::table('config_system')
            ->select('ID', 'Constraint', 'Value')
            ->orderBy('ID')
            ->get();

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function configSystemSubmit(Request $r)
    {
        $configs = $r->input('configs', []);

        $errors = [];
        foreach ($configs as $cfg) {
            $id    = $cfg['id'] ?? null;
            $value = $cfg['value'] ?? null;

            if (!$id) {
                $errors[] = 'ID wajib diisi';
                continue;
            }
            if ($value === '' || $value === null || !is_numeric($value)) {
                $errors[] = "Value untuk ID $id harus berupa angka";
                continue;
            }
        }

        if ($errors) {
            return response()->json(['status' => 'error', 'message' => implode('; ', $errors)], 422);
        }

        DB::transaction(function () use ($configs) {
            foreach ($configs as $cfg) {
                $id    = $cfg['id'] ?? null;
                $value = $cfg['value'] ?? null;
                if (!$id || $value === '' || $value === null || !is_numeric($value)) {
                    continue;
                }

                $updated = DB::table('config_system')
                    ->where('ID', $id)
                    ->update(['Value' => $value]);

                if ($updated === 0 && !DB::table('config_system')->where('ID', $id)->exists()) {
                    throw new \Exception("Konfigurasi ID $id tidak ditemukan");
                }
            }
        });

        return response()->json(['status' => 'success', 'message' => 'Konfigurasi berhasil disimpan']);
    }

    private function parseLat(string $coordinate): ?string
    {
        if (!preg_match("/^\\-?\\d+(?:\\.\\d+)?,\\-?\\d+(?:\\.\\d+)?$/", $coordinate)) {
            return null;
        }
        $parts = explode(",", $coordinate);
        return trim($parts[0]);
    }

    private function parseLng(string $coordinate): ?string
    {
        if (!preg_match("/^\\-?\\d+(?:\\.\\d+)?,\\-?\\d+(?:\\.\\d+)?$/", $coordinate)) {
            return null;
        }
        $parts = explode(",", $coordinate);
        return trim($parts[1]);
    }
}