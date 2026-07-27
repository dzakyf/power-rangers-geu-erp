<?php

namespace App\Http\Controllers\Credential;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Role;
use App\Permission;
use App\Services\RbacService;

class CredentialRoleController extends Controller
{
    public function index()
    {
        return view('credential.role');
    }

    public function roleListData(Request $request)
    {
        $query = Role::withCount('users');

        $total = $query->count();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where('name', 'like', "%{$search}%");
        }

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['id', 'name'];
        $query->orderBy($columns[$orderColumn] ?? 'id', $orderDir);

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $filtered = $query->count();

        $data = $query->offset($start)->limit($length)->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => $role->users_count,
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'records_total' => $total,
            'records_filtered' => $filtered,
            'data' => $data,
        ]);
    }

    public function rolePermissionsData(Request $request)
    {
        $roleId = $request->input('role_id');
        $role = Role::findOrFail($roleId);
        $rolePermissionNames = $role->permissions->pluck('name')->toArray();

        $allowedModules = ['FINANCE', 'HRD', 'MITRA', 'CNS'];

        $menus = DB::table('menu_lists')
            ->whereIn('Modules', $allowedModules)
            ->orderBy('Urutan', 'ASC')
            ->get();

        $total = $menus->count();

        $data = $menus->map(function ($menu) use ($rolePermissionNames) {
            $actions = ['view', 'create', 'edit', 'delete', 'approve'];
            $row = [
                'menu_id' => $menu->Menu_ID,
                'menu_name' => $menu->Name,
                'parent_menu' => $menu->Parent_Menu,
                'module' => $menu->Modules,
                'position' => $menu->Position,
            ];
            foreach ($actions as $action) {
                $permName = "menu.{$menu->Menu_ID}.{$action}";
                $row[$action] = in_array($permName, $rolePermissionNames);
            }
            return $row;
        });

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'records_total' => $total,
            'records_filtered' => $total,
            'data' => $data,
        ]);
    }

    public function savePermissions(Request $request)
    {
        if (!auth()->user()->can('master.credential.role.edit')) {
            abort(403, 'Only ADM can edit role permissions.');
        }

        $role = Role::findOrFail($request->input('role_id'));

        if ($role->name === 'ADM') {
            return response()->json(['success' => false, 'message' => 'ADM permissions are immutable.']);
        }

        $permissionInput = $request->input('permissions', []);

        $permissionNames = [];
        foreach ($permissionInput as $menuId => $actions) {
            foreach ($actions as $action => $checked) {
                if (filter_var($checked, FILTER_VALIDATE_BOOLEAN)) {
                    $permissionNames[] = "menu.{$menuId}.{$action}";
                }
            }
        }

        $permissions = Permission::whereIn('name', $permissionNames)->get();
        $role->syncPermissions($permissions);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($role->users as $user) {
            RbacService::rebuildSubMenu($user);
        }

        return response()->json(['success' => true]);
    }

    public function createRole(Request $request)
    {
        if (!auth()->user()->can('master.credential.role.edit')) {
            abort(403);
        }

        $name = trim($request->input('name'));
        if (!$name) {
            return response()->json(['success' => false, 'message' => 'Role name is required.']);
        }

        if (Role::where('name', $name)->exists()) {
            return response()->json(['success' => false, 'message' => 'Role name already exists.']);
        }

        Role::create(['name' => $name, 'guard_name' => 'web']);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['success' => true]);
    }

    public function updateRole(Request $request, $id)
    {
        if (!auth()->user()->can('master.credential.role.edit')) {
            abort(403);
        }

        $role = Role::findOrFail($id);

        if ($role->name === 'ADM') {
            return response()->json(['success' => false, 'message' => 'ADM role cannot be edited.']);
        }

        $name = trim($request->input('name'));
        if (!$name) {
            return response()->json(['success' => false, 'message' => 'Role name is required.']);
        }

        if (Role::where('name', $name)->where('id', '!=', $id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Role name already exists.']);
        }

        $role->update(['name' => $name]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['success' => true]);
    }

    public function deleteRole(Request $request, $id)
    {
        if (!auth()->user()->can('master.credential.role.edit')) {
            abort(403);
        }

        $role = Role::findOrFail($id);

        if ($role->name === 'ADM') {
            return response()->json(['success' => false, 'message' => 'ADM role cannot be deleted.']);
        }

        if ($role->users()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Role has users assigned. Remove users first.']);
        }

        $role->permissions()->detach();
        $role->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['success' => true]);
    }

    public function grantAllPermissions(Request $request, $id)
    {
        if (!auth()->user()->can('master.credential.role.edit')) {
            abort(403);
        }

        $role = Role::findOrFail($id);

        if ($role->name === 'ADM') {
            return response()->json(['success' => false, 'message' => 'ADM permissions are immutable.']);
        }

        $allowedModules = ['FINANCE', 'HRD', 'MITRA', 'CNS'];

        $menuIds = DB::table('menu_lists')
            ->whereIn('Modules', $allowedModules)
            ->pluck('Menu_ID');

        $permissionNames = [];
        foreach ($menuIds as $menuId) {
            foreach (['view', 'create', 'edit', 'delete', 'approve'] as $action) {
                $permissionNames[] = "menu.{$menuId}.{$action}";
            }
        }

        $permissions = Permission::whereIn('name', $permissionNames)->get();
        $role->syncPermissions($permissions);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($role->users as $user) {
            RbacService::rebuildSubMenu($user);
        }

        return response()->json(['success' => true]);
    }

    public function revokeAllPermissions(Request $request, $id)
    {
        if (!auth()->user()->can('master.credential.role.edit')) {
            abort(403);
        }

        $role = Role::findOrFail($id);

        if ($role->name === 'ADM') {
            return response()->json(['success' => false, 'message' => 'ADM permissions are immutable.']);
        }

        $role->syncPermissions([]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($role->users as $user) {
            DB::table('users')->where('id', $user->id)->update(['Sub_Menu' => '']);
        }

        return response()->json(['success' => true]);
    }

    public function updateMenuName(Request $request)
    {
        if (!auth()->user()->can('master.credential.role.edit')) {
            abort(403);
        }

        $menuId = $request->input('menu_id');
        $name = trim($request->input('name'));

        if (!$menuId || !$name) {
            return response()->json(['success' => false, 'message' => 'Menu ID and name are required.']);
        }

        DB::table('menu_lists')->where('Menu_ID', $menuId)->update(['Name' => $name]);

        return response()->json(['success' => true]);
    }
}
