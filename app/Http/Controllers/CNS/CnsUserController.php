<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Role as SpatieRole;
use App\Services\RbacService;
use App\Services\UploadService;
use Spatie\Permission\Models\Permission;

class CnsUserController extends Controller
{
    public function index()
    {
        $roles = SpatieRole::all()->pluck('name');
        $locations = DB::table('users')->select('Location')->distinct()->whereNotNull('Location')->pluck('Location');

        return view('cns.access.user.index', compact('roles', 'locations'));
    }

    public function userListData(Request $request)
    {
        $query = DB::table('users')->where('Main_Menu', 'CNS');

        $total = $query->count();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('Name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('Location', 'like', "%{$search}%");
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
                'index'    => $start + (++$index),
                'id'       => $u->id,
                'username' => $u->username,
                'name'     => $u->Name,
                'email'    => $u->email,
                'location' => $u->Location,
                'priv_id'  => $u->Priv_ID,
                'status'   => $u->Status,
                'roles'    => [],
                'signature_url' => $u->signature ? asset($u->signature) : null,
            ];
        }

        if (!empty($userIds)) {
            $roles = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->whereIn('model_has_roles.model_id', $userIds)
                ->where('model_has_roles.model_type', User::class)
                ->select('model_has_roles.model_id', 'roles.name')
                ->get();

            $rolesByUser = [];
            foreach ($roles as $r) {
                $rolesByUser[$r->model_id][] = $r->name;
            }

            foreach ($data as &$d) {
                $d['roles'] = $rolesByUser[$d['id']] ?? [];
            }
        }

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    public function createUserSubmit(Request $request)
    {
        $email = $request->input('email');
        $name = $request->input('Name');
        $password = $request->input('password', '12345678');
        $location = $request->input('Location', 'GEU-HO');
        $position = $request->input('Position', 'EMPLOYEE');

        if (!$email || !$name) {
            return response()->json(['success' => false, 'message' => 'Email/Username dan Name wajib diisi.'], 422);
        }

        $exists = DB::table('users')->where('username', $email)->first();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Username/Email sudah terdaftar.'], 422);
        }

        $userId = DB::table('users')->insertGetId([
            'username'  => $email,
            'Name'      => $name,
            'email'     => $email,
            'password'  => bcrypt($password),
            'Location'  => $location,
            'Position'  => $position,
            'Priv_ID'   => $position,
            'Status'    => 'ACTIVE',
            'Main_Menu' => 'CNS',
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
            $fileId = app(UploadService::class)->storeFile(
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

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        return response()->json(['success' => true]);
    }

    public function updateUser(Request $request, $id = null)
    {
        $userId = $id ?: $request->input('user_id');
        $user = User::findOrFail($userId);

        $name = $request->input('name') ?: $request->input('Name');
        $email = $request->input('email');
        $location = $request->input('location') ?: $request->input('Location');
        $status = $request->input('status') ?: $request->input('Status', 'ACTIVE');
        $primaryRole = $request->input('primary_role') ?: $request->input('Position');
        $additionalRoles = $request->input('additional_roles', []);

        $updateData = [
            'Name'      => $name,
            'email'     => $email,
            'Location'  => $location,
            'Status'    => $status,
            'Main_Menu' => 'CNS',
        ];

        if ($primaryRole) {
            $updateData['Priv_ID'] = $primaryRole;
            $updateData['Position'] = $primaryRole;
        }

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->input('password'));
        }

        DB::table('users')->where('id', $userId)->update($updateData);

        if ($primaryRole) {
            $allRoles = array_unique(array_filter(array_merge([$primaryRole], (array) $additionalRoles)));
            $roleModels = SpatieRole::whereIn('name', $allRoles)->get();
            $user->syncRoles($roleModels);
        }


        if ($request->hasFile('signature')) {
            $file = $request->file('signature');
            $fileId = app(UploadService::class)->storeFile(
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

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
        RbacService::rebuildSubMenu($user);

        return response()->json(['success' => true]);
    }

    public function deleteUser(Request $request, $id = null)
    {
        $userId = $id ?: $request->input('user_id');
        User::findOrFail($userId);

        DB::table('users')->where('id', $userId)->update([
            'Status' => 'INACTIVE',
        ]);

        return response()->json(['success' => true]);
    }
}
