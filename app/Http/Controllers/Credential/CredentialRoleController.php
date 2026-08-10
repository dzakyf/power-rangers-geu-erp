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

        $allowedModules = ['FINANCE', 'HRD', 'MITRA', 'CNS', 'CANDIDATE', 'CREDENTIAL'];

        $menus = DB::table('menu_lists')
            ->whereIn('Modules', $allowedModules)
            ->orderBy('Urutan', 'ASC')
            ->get();

        $total = $menus->count();

        $data = $menus->map(function ($menu) use ($rolePermissionNames) {
            $actions = ['view', 'create', 'edit', 'delete', 'approve'];
            $row = [
                'id' => $menu->ID,
                'menu_id' => $menu->Menu_ID,
                'menu_name' => $menu->Name,
                'parent_menu' => $menu->Parent_Menu,
                'module' => $menu->Modules,
                'position' => $menu->Position,
                'path_loc' => $menu->Path_Loc,
                'urutan' => $menu->Urutan,
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

        $permissionInput = $request->input('permissions', []);

        $permissionNames = [];
        foreach ($permissionInput as $menuId => $actions) {
            foreach ($actions as $action => $checked) {
                if (filter_var($checked, FILTER_VALIDATE_BOOLEAN)) {
                    $permissionNames[] = "menu.{$menuId}.{$action}";
                }
            }
        }

        // Preserve standalone permissions (not menu.*) so role/system-level capabilities survive the sync
        $standalone = $role->permissions()
            ->where('name', 'not like', 'menu.%')
            ->pluck('name')
            ->toArray();

        $permissionNames = array_merge($permissionNames, $standalone);

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

        $allowedModules = ['FINANCE', 'HRD', 'MITRA', 'CNS', 'CANDIDATE', 'CREDENTIAL'];

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

        $role->syncPermissions([]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($role->users as $user) {
            DB::table('users')->where('id', $user->id)->update(['Sub_Menu' => '']);
        }

        return response()->json(['success' => true]);
    }

    private function generateMenuId($module)
    {
        $prefix = strtoupper(substr($module, 0, 2));
        $maxId = DB::table('menu_lists')
            ->where('Menu_ID', 'LIKE', $prefix . '%')
            ->max('Menu_ID');
        $next = 1;
        if ($maxId && preg_match('/(\d+)$/', $maxId, $m)) {
            $next = (int) $m[1] + 1;
        }
        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    public function createMenuItem(Request $request)
    {
        if (!auth()->user()->can('master.credential.role.edit')) {
            abort(403);
        }

        $name = trim($request->input('name'));
        $module = trim($request->input('module'));
        $parentMenu = trim($request->input('parent_menu'));
        $position = (int) $request->input('position', 4);
        $pathLoc = trim($request->input('path_loc'));
        $urutan = (int) $request->input('urutan', 1);

        if (!$name || !$module) {
            return response()->json(['success' => false, 'message' => 'Name and Module are required.']);
        }
        if ($position < 1 || $position > 4) {
            $position = 4;
        }

        $menuId = $this->generateMenuId($module);

        DB::table('menu_lists')->insert([
            'Menu_ID'     => $menuId,
            'Name'        => $name,
            'Path_Loc'    => $pathLoc,
            'Modules'     => $module,
            'Position'    => $position,
            'Parent_Menu' => $parentMenu ?: '',
            'Urutan'      => $urutan,
        ]);

        foreach (['view', 'create', 'edit', 'delete', 'approve'] as $action) {
            Permission::firstOrCreate(
                ['name' => "menu.{$menuId}.{$action}", 'guard_name' => 'web'],
                ['menu_id' => $menuId, 'action' => $action]
            );
        }

        return response()->json(['success' => true, 'menu_id' => $menuId]);
    }

    public function updateMenuItem(Request $request)
    {
        if (!auth()->user()->can('master.credential.role.edit')) {
            abort(403);
        }

        $menuId = $request->input('menu_id');
        $rowId = $request->input('id');
        $name = trim($request->input('name'));
        $module = trim($request->input('module'));
        $parentMenu = trim($request->input('parent_menu'));
        $position = (int) $request->input('position', 4);
        $pathLoc = trim($request->input('path_loc'));
        $urutan = (int) $request->input('urutan', 1);

        if ((!$rowId && !$menuId) || !$name || !$module) {
            return response()->json(['success' => false, 'message' => 'Menu ID, Name and Module are required.']);
        }
        if ($position < 1 || $position > 4) {
            $position = 4;
        }

        $query = DB::table('menu_lists');
        if ($rowId) {
            $query->where('ID', $rowId);
        } else {
            $query->where('Menu_ID', $menuId);
        }
        $query->update([
            'Name'        => $name,
            'Path_Loc'    => $pathLoc,
            'Modules'     => $module,
            'Position'    => $position,
            'Parent_Menu' => $parentMenu ?: '',
            'Urutan'      => $urutan,
        ]);

        return response()->json(['success' => true]);
    }

    public function deleteMenuItem(Request $request)
    {
        if (!auth()->user()->can('master.credential.role.edit')) {
            abort(403);
        }

        $menuId = $request->input('menu_id');
        $rowId = $request->input('id');
        if (!$rowId && !$menuId) {
            return response()->json(['success' => false, 'message' => 'Menu ID is required.']);
        }

        $query = DB::table('menu_lists');
        if ($rowId) {
            $query->where('ID', $rowId);
        } else {
            $query->where('Menu_ID', $menuId);
        }
        $target = $query->first();

        if (!$target) {
            return response()->json(['success' => false, 'message' => 'Menu not found.']);
        }

        $children = DB::table('menu_lists')
            ->where('Parent_Menu', $target->Menu_ID)
            ->count();
        if ($children > 0) {
            return response()->json(['success' => false, 'message' => 'Menu has child items. Remove children first.']);
        }

        DB::table('menu_lists')->where('ID', $target->ID)->delete();

        Permission::where('name', 'like', "menu.{$target->Menu_ID}.%")->delete();

        return response()->json(['success' => true]);
    }
}
