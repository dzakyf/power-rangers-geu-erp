<?php

namespace App\Http\Controllers\Credential;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Role as SpatieRole;
use App\Services\RbacService;

class CredentialUserController extends Controller
{
    public function index()
    {
        $roles = SpatieRole::all()->pluck('name');
        return view('credential.user-management', compact('roles'));
    }

    public function createUser()
    {
        $roles = SpatieRole::all()->pluck('name');
        return view('credential.user-management', compact('roles'));
    }

    public function userListData(Request $request)
    {
        $query = DB::table('users');

        $total = $query->count();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('Name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['id', 'username', 'Name', 'email', 'Location', 'Priv_ID', 'Status'];
        $query->orderBy($columns[$orderColumn] ?? 'id', $orderDir);

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $filtered = $query->count();

        $userIds = [];
        $dbUsers = $query->offset($start)->limit($length)->get();
        $data = [];
        $index = 0;

        foreach ($dbUsers as $u) {
            $userIds[] = $u->id;
            $data[] = [
                'id' => $u->id,
                'no' => $start + $index + 1,
                'username' => $u->username,
                'name' => $u->Name,
                'email' => $u->email,
                'location' => $u->Location,
                'posisi' => $u->Priv_ID,
                'status' => $u->Status,
                'priv_id' => $u->Priv_ID,
                'signature' => !empty($u->signature) ? asset('filevendor/' . $u->signature) : null,
            ];
            $index++;
        }

        if (!empty($userIds)) {
            $userRoles = DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->whereIn('model_has_roles.model_id', $userIds)
                ->where('model_has_roles.model_type', 'App\User')
                ->select('model_has_roles.model_id', 'roles.name as role_name')
                ->get()
                ->groupBy('model_id');

            foreach ($data as &$row) {
                $uid = $row['id'];
                $rNames = isset($userRoles[$uid]) ? $userRoles[$uid]->pluck('role_name')->toArray() : [];
                $row['roles'] = $rNames;
            }
        }

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'records_total' => $total,
            'records_filtered' => $filtered,
            'data' => $data,
        ]);
    }

    public function candidatesWithoutAccount(Request $request)
    {
        $query = DB::table('recruitment as r')
            ->leftJoin('users as u', 'u.username', '=', 'r.Task_ID')
            ->whereNull('u.id')
            ->select('r.ID', 'r.Task_ID', 'r.Name', 'r.Position', 'r.Email', 'r.Handphone', 'r.Created_Date');

        $total = $query->count();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('r.Name', 'like', "%{$search}%")
                  ->orWhere('r.Task_ID', 'like', "%{$search}%");
            });
        }

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['ID', 'Task_ID', 'Name', 'Position', 'Email', 'Handphone', 'Created_Date'];
        $query->orderBy($columns[$orderColumn] ?? 'ID', $orderDir);

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $filtered = $query->count();

        $data = $query->offset($start)->limit($length)->get()->map(function ($c) {
            return [
                'id' => $c->ID,
                'task_id' => $c->Task_ID,
                'name' => $c->Name,
                'position' => $c->Position,
                'email' => $c->Email,
                'handphone' => $c->Handphone,
                'created_date' => $c->Created_Date,
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'records_total' => $total,
            'records_filtered' => $filtered,
            'data' => $data,
        ]);
    }

    public function createCandidateAccount(Request $request)
    {
        $taskId = $request->input('Task_ID');
        if (!$taskId) {
            return 'error:Task_ID is required';
        }

        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();
        if (!$rec) {
            return 'error:Candidate not found';
        }

        $exists = DB::table('users')->where('username', $taskId)->first();
        if ($exists) {
            return 'error:User account already exists';
        }

        DB::table('users')->insert([
            'username' => $rec->Task_ID,
            'Emp_ID'   => $rec->Task_ID,
            'password' => bcrypt('12345678'),
            'Name'     => $rec->Name,
            'email'    => $rec->Email,
            'Location' => $rec->Location ?? 'GEU-HO',
            'Position' => 'CANDIDATE',
            'Status'   => 'ACTIVE',
            'Sub_Menu' => 'CI00001,CI00002,CI00003,CI00004,CI00005',
        ]);

        return "success:Account created for {$rec->Name}";
    }

    public function createUserSubmit(Request $request)
    {
        $email = $request->input('email');
        $name = $request->input('Name');
        $password = $request->input('password', '12345678');
        $location = $request->input('Location', 'GEU-HO');
        $position = $request->input('Position', 'EMPLOYEE');

        if (!$email || !$name) {
            return 'error:Email/Username and Name are required';
        }

        $exists = DB::table('users')->where('username', $email)->first();
        if ($exists) {
            return 'error:Email/Username already exists';
        }
        $userId = DB::table('users')->insertGetId([
            'username' => $email,
            'Name'     => $name,
            'email'    => $email,
            'password' => bcrypt($password),
            'Location' => $location,
            'Position' => $position,
            'Priv_ID'  => $position,
            'Status'   => 'ACTIVE',
        ]);

        $user = User::find($userId);
        if ($user && $position) {
            $roleModel = SpatieRole::where('name', $position)->first();
            if ($roleModel) {
                $user->assignRole($roleModel);
                RbacService::rebuildSubMenu($user);
            }
        }

        if ($request->hasFile('signature')) {
            $file = $request->file('signature');
            $fileId = app(\App\Services\UploadService::class)->storeFile(
                $file,
                'file',
                'user-signature',
                (string) $userId,
                'Signature'
            );
            $path = DB::table('meta_files')->where('ID', $fileId)->value('Path');
            if ($path) {
                DB::table('users')->where('id', $userId)->update(['signature' => $path]);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return 'success';
    }

    public function updateUser(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::findOrFail($userId);

        $name = $request->input('name');
        $email = $request->input('email');
        $location = $request->input('location');
        $status = $request->input('status');
        $primaryRole = $request->input('primary_role');
        $additionalRoles = $request->input('additional_roles', []);

        DB::table('users')->where('id', $userId)->update([
            'Name'     => $name,
            'email'    => $email,
            'Location' => $location,
            'Status'   => $status,
            'Priv_ID'  => $primaryRole,
            'Position' => $primaryRole,
        ]);

        $allRoles = array_unique(array_merge([$primaryRole], $additionalRoles));
        $roleModels = SpatieRole::whereIn('name', $allRoles)->get();
        $user->syncRoles($roleModels);

        if ($request->hasFile('signature')) {
            $file = $request->file('signature');
            $fileId = app(\App\Services\UploadService::class)->storeFile(
                $file,
                'file',
                'user-signature',
                (string) $userId,
                'Signature'
            );
            $path = DB::table('meta_files')->where('ID', $fileId)->value('Path');
            if ($path) {
                DB::table('users')->where('id', $userId)->update(['signature' => $path]);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        RbacService::rebuildSubMenu($user);

        return response()->json(['success' => true]);
    }

    public function deleteUser(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::findOrFail($userId);

        DB::table('users')->where('id', $userId)->update([
            'Status' => 'INACTIVE',
        ]);

        return response()->json(['success' => true]);
    }
}
