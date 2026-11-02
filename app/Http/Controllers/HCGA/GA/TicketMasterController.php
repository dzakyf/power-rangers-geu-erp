<?php

namespace App\Http\Controllers\HCGA\GA;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TicketMasterController extends Controller
{
    use GaXml;

    public function index()
    {
        abort_unless(can_menu('SI01012', 'view'), 403);

        $categories = DB::table('ga_ticket_category')->orderBy('Name')->get();
        $teams = DB::table('ga_team')->where('Status', 'ACTIVE')->orderBy('Name')->get();
        $slas = DB::table('ga_sla_policy')->orderBy('Name')->get();
        $vendors = DB::table('supplier')->where('Status', 'ACTIVE')->orderBy('Company_Name')->limit(500)->get(['ID', 'Company_Name']);
        $users = $this->memberUsers();

        return view('humanresources.ga.master.index', compact('categories', 'teams', 'slas', 'vendors', 'users'));
    }

    public function categoryData()
    {
        abort_unless(can_menu('SI01012', 'view'), 403);

        $rows = DB::table('ga_ticket_category as c')
            ->leftJoin('ga_ticket_category as p', 'p.ID', '=', 'c.Parent_ID')
            ->leftJoin('ga_team as t', 't.ID', '=', 'c.Default_Team_ID')
            ->leftJoin('ga_sla_policy as s', 's.ID', '=', 'c.Sla_Policy_ID')
            ->orderBy('c.Name')
            ->select('c.*', 'p.Name as Parent_Name', 't.Name as Team_Name', 's.Name as Sla_Name')
            ->get();

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Code) . '</cell>';
            echo '<cell>' . $this->xml($row->Name) . '</cell>';
            echo '<cell>' . $this->xml($row->Parent_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Default_Priority) . '</cell>';
            echo '<cell>' . $this->xml($row->Team_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Sla_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Status) . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function saveCategory(Request $request)
    {
        $action = $request->filled('ID') ? 'edit' : 'create';
        abort_unless(can_menu('SI01012', $action), 403);

        $data = $request->validate([
            'ID' => 'nullable|integer',
            'Code' => 'required|string|max:50',
            'Name' => 'required|string|max:150',
            'Parent_ID' => 'nullable|integer',
            'Default_Priority' => 'nullable|in:LOW,NORMAL,HIGH,URGENT',
            'Default_Team_ID' => 'nullable|integer',
            'Sla_Policy_ID' => 'nullable|integer',
            'Status' => 'nullable|in:ACTIVE,INACTIVE',
        ]);

        try {
            $now = Carbon::now();
            $user = Auth::user();
            $payload = [
                'Location' => $user->Location,
                'Company_ID' => $user->Company_ID,
                'Parent_ID' => !empty($data['Parent_ID']) ? (int) $data['Parent_ID'] : null,
                'Code' => strtoupper(trim((string) $data['Code'])),
                'Name' => trim((string) $data['Name']),
                'Default_Priority' => $data['Default_Priority'] ?? 'NORMAL',
                'Default_Team_ID' => !empty($data['Default_Team_ID']) ? (int) $data['Default_Team_ID'] : null,
                'Sla_Policy_ID' => !empty($data['Sla_Policy_ID']) ? (int) $data['Sla_Policy_ID'] : null,
                'Status' => $data['Status'] ?? 'ACTIVE',
                'Updated_By' => (string) $user->username,
                'Updated_At' => $now,
            ];

            if (!empty($data['ID'])) {
                DB::table('ga_ticket_category')->where('ID', (int) $data['ID'])->update($payload);
                $id = (int) $data['ID'];
            } else {
                $payload['Created_By'] = (string) $user->username;
                $payload['Created_At'] = $now;
                $id = DB::table('ga_ticket_category')->insertGetId($payload);
            }

            return response()->json(['status' => 'success', 'message' => 'Kategori disimpan.', 'id' => $id]);
        } catch (\Throwable $exception) {
            Log::warning('GA category save failed', ['message' => $exception->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function deleteCategory(Request $request)
    {
        abort_unless(can_menu('SI01012', 'delete'), 403);

        $data = $request->validate(['ID' => 'required|integer']);
        DB::table('ga_ticket_category')->where('ID', (int) $data['ID'])->update([
            'Status' => 'INACTIVE',
            'Updated_By' => (string) Auth::user()->username,
            'Updated_At' => Carbon::now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Kategori dinonaktifkan.']);
    }

    public function slaData()
    {
        abort_unless(can_menu('SI01012', 'view'), 403);

        $rows = DB::table('ga_sla_policy as s')
            ->leftJoin('ga_ticket_category as c', 'c.ID', '=', 's.Category_ID')
            ->orderBy('s.Name')
            ->select('s.*', 'c.Name as Category_Name')
            ->get();

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Code) . '</cell>';
            echo '<cell>' . $this->xml($row->Name) . '</cell>';
            echo '<cell>' . $this->xml($row->Category_Name ?: 'Semua') . '</cell>';
            echo '<cell>' . $this->xml($row->Priority ?: 'Semua') . '</cell>';
            echo '<cell>' . $this->xml($row->Response_Minutes) . '</cell>';
            echo '<cell>' . $this->xml($row->Resolve_Minutes) . '</cell>';
            echo '<cell>' . $this->xml($row->Status) . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function saveSla(Request $request)
    {
        $action = $request->filled('ID') ? 'edit' : 'create';
        abort_unless(can_menu('SI01012', $action), 403);

        $data = $request->validate([
            'ID' => 'nullable|integer',
            'Code' => 'required|string|max:50',
            'Name' => 'required|string|max:150',
            'Category_ID' => 'nullable|integer',
            'Priority' => 'nullable|in:LOW,NORMAL,HIGH,URGENT',
            'Response_Minutes' => 'required|integer|min:1',
            'Resolve_Minutes' => 'required|integer|min:1',
            'Status' => 'nullable|in:ACTIVE,INACTIVE',
        ]);

        try {
            $now = Carbon::now();
            $user = Auth::user();
            $payload = [
                'Location' => $user->Location,
                'Company_ID' => $user->Company_ID,
                'Code' => strtoupper(trim((string) $data['Code'])),
                'Name' => trim((string) $data['Name']),
                'Category_ID' => !empty($data['Category_ID']) ? (int) $data['Category_ID'] : null,
                'Priority' => !empty($data['Priority']) ? strtoupper((string) $data['Priority']) : null,
                'Response_Minutes' => (int) $data['Response_Minutes'],
                'Resolve_Minutes' => (int) $data['Resolve_Minutes'],
                'Status' => $data['Status'] ?? 'ACTIVE',
                'Updated_By' => (string) $user->username,
                'Updated_At' => $now,
            ];

            if (!empty($data['ID'])) {
                DB::table('ga_sla_policy')->where('ID', (int) $data['ID'])->update($payload);
                $id = (int) $data['ID'];
            } else {
                $payload['Created_By'] = (string) $user->username;
                $payload['Created_At'] = $now;
                $id = DB::table('ga_sla_policy')->insertGetId($payload);
            }

            return response()->json(['status' => 'success', 'message' => 'SLA disimpan.', 'id' => $id]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function teamData()
    {
        abort_unless(can_menu('SI01012', 'view'), 403);

        $rows = DB::table('ga_team as t')
            ->leftJoin('supplier as v', 'v.ID', '=', 't.Supplier_ID')
            ->orderBy('t.Name')
            ->select('t.*', 'v.Company_Name as Vendor_Name')
            ->get();

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Code) . '</cell>';
            echo '<cell>' . $this->xml($row->Name) . '</cell>';
            echo '<cell>' . $this->xml($row->Team_Type) . '</cell>';
            echo '<cell>' . $this->xml($row->Vendor_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Department ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Status) . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function saveTeam(Request $request)
    {
        $action = $request->filled('ID') ? 'edit' : 'create';
        abort_unless(can_menu('SI01012', $action), 403);

        $data = $request->validate([
            'ID' => 'nullable|integer',
            'Code' => 'required|string|max:50',
            'Name' => 'required|string|max:150',
            'Team_Type' => 'required|in:INTERNAL,VENDOR',
            'Supplier_ID' => 'nullable|integer',
            'Department' => 'nullable|string|max:100',
            'Status' => 'nullable|in:ACTIVE,INACTIVE',
        ]);

        try {
            $now = Carbon::now();
            $user = Auth::user();
            $payload = [
                'Location' => $user->Location,
                'Company_ID' => $user->Company_ID,
                'Code' => strtoupper(trim((string) $data['Code'])),
                'Name' => trim((string) $data['Name']),
                'Team_Type' => strtoupper((string) $data['Team_Type']),
                'Supplier_ID' => !empty($data['Supplier_ID']) ? (int) $data['Supplier_ID'] : null,
                'Department' => !empty($data['Department']) ? mb_substr((string) $data['Department'], 0, 100) : null,
                'Status' => $data['Status'] ?? 'ACTIVE',
                'Updated_By' => (string) $user->username,
                'Updated_At' => $now,
            ];

            if (!empty($data['ID'])) {
                DB::table('ga_team')->where('ID', (int) $data['ID'])->update($payload);
                $id = (int) $data['ID'];
            } else {
                $payload['Created_By'] = (string) $user->username;
                $payload['Created_At'] = $now;
                $id = DB::table('ga_team')->insertGetId($payload);
            }

            return response()->json(['status' => 'success', 'message' => 'Team disimpan.', 'id' => $id]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Daftar user ERP untuk pilihan anggota team. Nama tabel dan primary key diambil
     * dari model user yang sedang login, jadi tidak perlu hardcode nama tabel user.
     */
    private function memberUsers()
    {
        $model = Auth::user();
        $table = $model->getTable();
        $key = $model->getKeyName();
        $hasName = Schema::hasColumn($table, 'name');

        $columns = [$key . ' as ID', 'username'];
        if ($hasName) {
            $columns[] = 'name';
        }

        return DB::table($table)->orderBy('username')->limit(2000)->get($columns)->map(function ($u) use ($hasName) {
            $label = (string) $u->username;
            if ($hasName && !empty($u->name) && $u->name !== $u->username) {
                $label .= ' - ' . $u->name;
            }

            return (object) ['ID' => $u->ID, 'Label' => $label];
        });
    }

    public function memberData(Request $request)
    {
        abort_unless(can_menu('SI01012', 'view'), 403);

        $model = Auth::user();
        $userTable = $model->getTable();
        $userKey = $model->getKeyName();
        $hasName = Schema::hasColumn($userTable, 'name');

        $query = DB::table('ga_team_member as m')
            ->leftJoin('ga_team as t', 't.ID', '=', 'm.Team_ID')
            ->leftJoin($userTable . ' as u', 'u.' . $userKey, '=', 'm.User_Id')
            ->leftJoin('supplier as v', 'v.ID', '=', 'm.Supplier_ID')
            ->orderBy('t.Name')
            ->orderByRaw("FIELD(m.Role_In_Team, 'LEADER', 'MEMBER')")
            ->orderBy('m.ID')
            ->select('m.*', 't.Name as Team_Name', 'u.username as User_Username', 'v.Company_Name as Vendor_Name');

        if ($hasName) {
            $query->addSelect('u.name as User_Name');
        }
        if ($request->filled('Team_ID')) {
            $query->where('m.Team_ID', (int) $request->input('Team_ID'));
        }

        $rows = $query->get();

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            if (!empty($row->User_Id)) {
                $type = 'USER';
                $display = (string) $row->User_Username;
                if ($hasName && !empty($row->User_Name) && $row->User_Name !== $row->User_Username) {
                    $display .= ' - ' . $row->User_Name;
                }
            } else {
                $type = 'VENDOR';
                $display = (string) $row->Vendor_Name;
            }

            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Team_Name ?: '-') . '</cell>';
            echo '<cell>' . $type . '</cell>';
            echo '<cell>' . $this->xml($display !== '' ? $display : '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Role_In_Team ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Status) . '</cell>';
            echo '<cell>' . (int) $row->Team_ID . '</cell>';
            echo '<cell>' . ($row->User_Id ? (int) $row->User_Id : '') . '</cell>';
            echo '<cell>' . ($row->Supplier_ID ? (int) $row->Supplier_ID : '') . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function saveMember(Request $request)
    {
        $action = $request->filled('ID') ? 'edit' : 'create';
        abort_unless(can_menu('SI01012', $action), 403);

        $data = $request->validate([
            'ID' => 'nullable|integer',
            'Team_ID' => 'required|integer',
            'User_Id' => 'nullable|integer',
            'Supplier_ID' => 'nullable|integer',
            'Role_In_Team' => 'required|in:LEADER,MEMBER',
            'Status' => 'nullable|in:ACTIVE,INACTIVE',
        ]);

        try {
            $userId = !empty($data['User_Id']) ? (int) $data['User_Id'] : null;
            $supplierId = !empty($data['Supplier_ID']) ? (int) $data['Supplier_ID'] : null;

            if (($userId === null) === ($supplierId === null)) {
                return response()->json(['status' => 'error', 'message' => 'Pilih salah satu: User ERP (internal) atau Vendor.'], 422);
            }
            if (!DB::table('ga_team')->where('ID', (int) $data['Team_ID'])->exists()) {
                return response()->json(['status' => 'error', 'message' => 'Team tidak ditemukan.'], 422);
            }

            $model = Auth::user();
            if ($userId !== null && !DB::table($model->getTable())->where($model->getKeyName(), $userId)->exists()) {
                return response()->json(['status' => 'error', 'message' => 'User tidak ditemukan.'], 422);
            }
            if ($supplierId !== null && !DB::table('supplier')->where('ID', $supplierId)->exists()) {
                return response()->json(['status' => 'error', 'message' => 'Vendor tidak ditemukan.'], 422);
            }

            // Unique key (Team_ID, User_Id) berlaku untuk semua status, jadi cek tanpa melihat Status.
            $duplicate = DB::table('ga_team_member')
                ->where('Team_ID', (int) $data['Team_ID'])
                ->when($userId !== null, fn ($q) => $q->where('User_Id', $userId), fn ($q) => $q->where('Supplier_ID', $supplierId))
                ->when(!empty($data['ID']), fn ($q) => $q->where('ID', '!=', (int) $data['ID']))
                ->first(['ID', 'Status']);
            if ($duplicate) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anggota ini sudah terdaftar di team tersebut (status ' . $duplicate->Status . '). Ubah data yang sudah ada.',
                ], 422);
            }

            $payload = [
                'Team_ID' => (int) $data['Team_ID'],
                'User_Id' => $userId,
                'Supplier_ID' => $supplierId,
                'Role_In_Team' => strtoupper((string) $data['Role_In_Team']),
                'Status' => $data['Status'] ?? 'ACTIVE',
            ];

            if (!empty($data['ID'])) {
                DB::table('ga_team_member')->where('ID', (int) $data['ID'])->update($payload);
                $id = (int) $data['ID'];
            } else {
                $id = DB::table('ga_team_member')->insertGetId($payload);
            }

            return response()->json(['status' => 'success', 'message' => 'Anggota team disimpan.', 'id' => $id]);
        } catch (\Throwable $exception) {
            Log::warning('GA team member save failed', ['message' => $exception->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function deleteMember(Request $request)
    {
        abort_unless(can_menu('SI01012', 'delete'), 403);

        $data = $request->validate(['ID' => 'required|integer']);
        DB::table('ga_team_member')->where('ID', (int) $data['ID'])->update(['Status' => 'INACTIVE']);

        return response()->json(['status' => 'success', 'message' => 'Anggota team dinonaktifkan.']);
    }

    public function routingData()
    {
        abort_unless(can_menu('SI01012', 'view'), 403);

        $rows = DB::table('ga_routing_rule as r')
            ->leftJoin('ga_ticket_category as c', 'c.ID', '=', 'r.Category_ID')
            ->leftJoin('ga_ticket_category as sc', 'sc.ID', '=', 'r.Subcategory_ID')
            ->leftJoin('ga_team as t', 't.ID', '=', 'r.Team_ID')
            ->orderBy('r.Sort_Order')
            ->select('r.*', 'c.Name as Category_Name', 'sc.Name as Subcategory_Name', 't.Name as Team_Name')
            ->get();

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Category_Name ?: 'Semua') . '</cell>';
            echo '<cell>' . $this->xml($row->Subcategory_Name ?: 'Semua') . '</cell>';
            echo '<cell>' . $this->xml($row->Priority ?: 'Semua') . '</cell>';
            echo '<cell>' . $this->xml($row->Team_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Sort_Order) . '</cell>';
            echo '<cell>' . $this->xml($row->Is_Active ? 'YES' : 'NO') . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function saveRouting(Request $request)
    {
        $action = $request->filled('ID') ? 'edit' : 'create';
        abort_unless(can_menu('SI01012', $action), 403);

        $data = $request->validate([
            'ID' => 'nullable|integer',
            'Category_ID' => 'nullable|integer',
            'Subcategory_ID' => 'nullable|integer',
            'Priority' => 'nullable|in:LOW,NORMAL,HIGH,URGENT',
            'Team_ID' => 'required|integer',
            'Sort_Order' => 'nullable|integer|min:0',
            'Is_Active' => 'nullable|boolean',
        ]);

        try {
            $now = Carbon::now();
            $user = Auth::user();
            $payload = [
                'Location' => $user->Location,
                'Company_ID' => $user->Company_ID,
                'Category_ID' => !empty($data['Category_ID']) ? (int) $data['Category_ID'] : null,
                'Subcategory_ID' => !empty($data['Subcategory_ID']) ? (int) $data['Subcategory_ID'] : null,
                'Priority' => !empty($data['Priority']) ? strtoupper((string) $data['Priority']) : null,
                'Team_ID' => (int) $data['Team_ID'],
                'Sort_Order' => isset($data['Sort_Order']) ? (int) $data['Sort_Order'] : 0,
                'Is_Active' => !empty($data['Is_Active']) ? 1 : 0,
                'Updated_By' => (string) $user->username,
                'Updated_At' => $now,
            ];

            if (!empty($data['ID'])) {
                DB::table('ga_routing_rule')->where('ID', (int) $data['ID'])->update($payload);
                $id = (int) $data['ID'];
            } else {
                $payload['Created_By'] = (string) $user->username;
                $payload['Created_At'] = $now;
                $id = DB::table('ga_routing_rule')->insertGetId($payload);
            }

            return response()->json(['status' => 'success', 'message' => 'Routing disimpan.', 'id' => $id]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function options()
    {
        abort_unless(can_menu('SI01012', 'view'), 403);

        return response()->json([
            'categories' => DB::table('ga_ticket_category')->where('Status', 'ACTIVE')->orderBy('Name')->get(['ID', 'Parent_ID', 'Code', 'Name']),
            'teams' => DB::table('ga_team')->where('Status', 'ACTIVE')->orderBy('Name')->get(['ID', 'Code', 'Name', 'Team_Type']),
            'slas' => DB::table('ga_sla_policy')->where('Status', 'ACTIVE')->orderBy('Name')->get(['ID', 'Code', 'Name']),
            'vendors' => DB::table('supplier')->where('Status', 'ACTIVE')->orderBy('Company_Name')->limit(500)->get(['ID', 'Company_Name']),
            'users' => $this->memberUsers(),
        ]);
    }
}
