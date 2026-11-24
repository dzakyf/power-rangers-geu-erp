<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Role;
use App\Permission;
use App\Services\RbacService;
use App\Services\CNS\CnsPermissionService;

class CnsRoleController extends Controller
{
    private const MENU_ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    private function canEditRoles(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->hasRole('ADM')
            || $user->username === 'admin'
            || $user->can('cns.role_management.edit')
            || $user->can('master.credential.role.edit');
    }

    public function index()
    {
        return view('cns.access.role.index');
    }

    public function roleListData(Request $request)
    {
        $query = Role::withCount(['users' => function ($q) {
            $q->where('Main_Menu', 'CNS');
        }]);

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

        $roles = $query->offset($start)->limit($length)->get();

        $data = $roles->map(function ($r) {
            return [
                'id'          => $r->id,
                'name'        => $r->name,
                'users_count' => $r->users_count,
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    public function rolePermissionsData(Request $request)
    {
        $roleId = $request->input('role_id');
        $role = Role::findOrFail($roleId);
        $rolePermissionNames = $role->permissions->pluck('name')->toArray();

        // Scope strictly to CNS module menus, exclude Position <= 2 (MS module root & SM menu induk)
        $menus = DB::table('menu_lists')
            ->where('Modules', 'CNS')
            ->where('Position', '>', 2)
            ->orderBy('Position', 'ASC')
            ->orderBy('Urutan', 'ASC')
            ->get();

        $total = $menus->count();

        $data = $menus->map(function ($menu) use ($rolePermissionNames) {
            $row = [
                'id'          => $menu->ID,
                'menu_id'     => $menu->Menu_ID,
                'menu_name'   => $menu->Name,
                'parent_menu' => $menu->Parent_Menu,
                'module'      => $menu->Modules,
                'position'    => $menu->Position,
                'path_loc'    => $menu->Path_Loc,
                'urutan'      => $menu->Urutan,
            ];
            foreach (self::MENU_ACTIONS as $action) {
                $permName = CnsPermissionService::isCnsMenu($menu->Menu_ID)
                    ? CnsPermissionService::permissionName($menu->Menu_ID, $action)
                    : "menu.{$menu->Menu_ID}.{$action}";
                $row[$action] = in_array($permName, $rolePermissionNames, true);
            }
            return $row;
        });

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $data,
        ]);
    }

    public function savePermissions(Request $request)
    {
        if (!$this->canEditRoles()) {
            abort(403, 'Akses ditolak: Hanya admin yang dapat mengubah hak akses.');
        }

        $role = Role::findOrFail($request->input('role_id'));
        $permissionInput = $request->input('permissions', []);

        $menus = DB::table('menu_lists')->where('Modules', 'CNS')->get()->keyBy('Menu_ID');

        $newCnsPermissions = [];
        $newPermMetadata = [];
        foreach ($permissionInput as $menuId => $actions) {
            // If any ancestor folder was explicitly unchecked, prune child permissions
            $ancestorDisabled = false;
            $curr = $menuId;
            while ($curr && isset($menus[$curr])) {
                $parent = $menus[$curr]->Parent_Menu;
                if ($parent && isset($menus[$parent])) {
                    if (isset($permissionInput[$parent])) {
                        $parentActions = $permissionInput[$parent];
                        if (empty($parentActions['view']) || !filter_var($parentActions['view'], FILTER_VALIDATE_BOOLEAN)) {
                            $ancestorDisabled = true;
                            break;
                        }
                    }
                }
                $curr = $parent;
            }

            if ($ancestorDisabled) {
                continue;
            }

            $isCns = CnsPermissionService::isCnsMenu($menuId);
            foreach ($actions as $action => $checked) {
                if (filter_var($checked, FILTER_VALIDATE_BOOLEAN)) {
                    $pName = $isCns
                        ? CnsPermissionService::permissionName($menuId, $action)
                        : "menu.{$menuId}.{$action}";
                    $newCnsPermissions[] = $pName;
                    $newPermMetadata[$pName] = [
                        'menu_id' => $menuId,
                        'action'  => $action,
                    ];
                }
            }
        }

        // All possible CNS menu permissions
        $allPossibleCnsPerms = [];
        foreach (CnsPermissionService::allMenuSlugs() as $mId => $slug) {
            foreach (self::MENU_ACTIONS as $act) {
                $allPossibleCnsPerms[] = "cns.{$slug}.{$act}";
                $allPossibleCnsPerms[] = "menu.{$mId}.{$act}";
            }
        }

        // Preserve permissions that do NOT belong to CNS menus
        $preservedPermissions = $role->permissions()
            ->whereNotIn('name', $allPossibleCnsPerms)
            ->pluck('name')
            ->toArray();

        $finalPermissionNames = array_unique(array_merge($preservedPermissions, $newCnsPermissions));

        // Ensure permission records exist in database with menu_id and action
        foreach ($newCnsPermissions as $pName) {
            $meta = $newPermMetadata[$pName] ?? [];
            Permission::firstOrCreate(
                ['name' => $pName, 'guard_name' => 'web'],
                ['menu_id' => $meta['menu_id'] ?? null, 'action' => $meta['action'] ?? null]
            );
            if (!empty($meta['menu_id']) && !empty($meta['action'])) {
                Permission::where('name', $pName)
                    ->where(function ($q) {
                        $q->whereNull('menu_id')->orWhereNull('action');
                    })
                    ->update([
                        'menu_id' => $meta['menu_id'],
                        'action'  => $meta['action'],
                    ]);
            }
        }

        $permissions = Permission::whereIn('name', $finalPermissionNames)->get();
        $role->syncPermissions($permissions);

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        foreach ($role->users as $user) {
            RbacService::rebuildSubMenu($user);
        }

        return response()->json(['success' => true]);
    }

    public function createRole(Request $request)
    {
        if (!$this->canEditRoles()) {
            abort(403);
        }

        $name = trim((string) $request->input('name'));
        if ($name === '') {
            return response()->json(['success' => false, 'message' => 'Nama Role wajib diisi.'], 422);
        }

        if (Role::where('name', $name)->exists()) {
            return response()->json(['success' => false, 'message' => 'Role sudah ada.'], 422);
        }

        $role = Role::create(['name' => $name, 'guard_name' => 'web']);

        return response()->json(['success' => true, 'role' => $role]);
    }

    public function updateRole(Request $request, $id)
    {
        if (!$this->canEditRoles()) {
            abort(403);
        }

        $role = Role::findOrFail($id);
        $name = trim((string) $request->input('name'));

        if ($name === '') {
            return response()->json(['success' => false, 'message' => 'Nama Role wajib diisi.'], 422);
        }

        if (Role::where('name', $name)->where('id', '!=', $id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Nama Role sudah digunakan.'], 422);
        }

        $role->update(['name' => $name]);

        return response()->json(['success' => true]);
    }

    public function deleteRole(Request $request, $id)
    {
        if (!$this->canEditRoles()) {
            abort(403);
        }

        $role = Role::findOrFail($id);
        if ($role->name === 'ADM' || $role->name === 'Admin' || $role->name === 'admin') {
            return response()->json(['success' => false, 'message' => 'Role default admin tidak dapat dihapus.'], 403);
        }

        $role->delete();

        return response()->json(['success' => true]);
    }

    public function grantAllPermissions(Request $request, $id)
    {
        if (!$this->canEditRoles()) {
            abort(403);
        }

        $role = Role::findOrFail($id);

        $menus = DB::table('menu_lists')
            ->where('Modules', 'CNS')
            ->get();

        $cnsPermissionNames = [];
        $cnsPermMetadata = [];
        foreach ($menus as $menu) {
            $isCns = CnsPermissionService::isCnsMenu($menu->Menu_ID);
            foreach (self::MENU_ACTIONS as $action) {
                $pName = $isCns
                    ? CnsPermissionService::permissionName($menu->Menu_ID, $action)
                    : "menu.{$menu->Menu_ID}.{$action}";
                $cnsPermissionNames[] = $pName;
                $cnsPermMetadata[$pName] = [
                    'menu_id' => $menu->Menu_ID,
                    'action'  => $action,
                ];
            }
        }

        foreach ($cnsPermissionNames as $pName) {
            $meta = $cnsPermMetadata[$pName] ?? [];
            Permission::firstOrCreate(
                ['name' => $pName, 'guard_name' => 'web'],
                ['menu_id' => $meta['menu_id'] ?? null, 'action' => $meta['action'] ?? null]
            );
            if (!empty($meta['menu_id']) && !empty($meta['action'])) {
                Permission::where('name', $pName)
                    ->where(function ($q) {
                        $q->whereNull('menu_id')->orWhereNull('action');
                    })
                    ->update([
                        'menu_id' => $meta['menu_id'],
                        'action'  => $meta['action'],
                    ]);
            }
        }

        // Merge with existing non-CNS permissions
        $allPossibleCnsPerms = [];
        foreach (CnsPermissionService::allMenuSlugs() as $mId => $slug) {
            foreach (self::MENU_ACTIONS as $act) {
                $allPossibleCnsPerms[] = "cns.{$slug}.{$act}";
                $allPossibleCnsPerms[] = "menu.{$mId}.{$act}";
            }
        }

        $preserved = $role->permissions()
            ->whereNotIn('name', $allPossibleCnsPerms)
            ->pluck('name')
            ->toArray();

        $finalNames = array_unique(array_merge($preserved, $cnsPermissionNames));
        $permissions = Permission::whereIn('name', $finalNames)->get();
        $role->syncPermissions($permissions);

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        foreach ($role->users as $user) {
            RbacService::rebuildSubMenu($user);
        }

        return response()->json(['success' => true]);
    }

    public function revokeAllPermissions(Request $request, $id)
    {
        if (!$this->canEditRoles()) {
            abort(403);
        }

        $role = Role::findOrFail($id);

        $allPossibleCnsPerms = [];
        foreach (CnsPermissionService::allMenuSlugs() as $mId => $slug) {
            foreach (self::MENU_ACTIONS as $act) {
                $allPossibleCnsPerms[] = "cns.{$slug}.{$act}";
                $allPossibleCnsPerms[] = "menu.{$mId}.{$act}";
            }
        }

        // Revoke only CNS permissions, keep non-CNS
        $preserved = $role->permissions()
            ->whereNotIn('name', $allPossibleCnsPerms)
            ->pluck('name')
            ->toArray();

        // Never lock out ADM
        if ($role->name === 'ADM') {
            $masterPerm = Permission::where('name', 'master.credential.role.edit')->first();
            if ($masterPerm && !in_array('master.credential.role.edit', $preserved, true)) {
                $preserved[] = 'master.credential.role.edit';
            }
        }

        $permissions = Permission::whereIn('name', $preserved)->get();
        $role->syncPermissions($permissions);

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        foreach ($role->users as $user) {
            RbacService::rebuildSubMenu($user);
        }

        return response()->json(['success' => true]);
    }
}
