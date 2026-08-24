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
    /**
     * Modules exposed by the credential-role permission editor.
     */
    private const ALLOWED_MODULES = ['FINANCE', 'HRD', 'MITRA', 'CNS', 'CANDIDATE', 'CREDENTIAL'];

    /**
     * CRUD actions granted as menu.{Menu_ID}.{action} permissions.
     */
    private const MENU_ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    /**
     * Editing is granted to holders of the granular permission, and always to
     * the ADM role itself so admins can never be permanently locked out of
     * this editor (see revokeAllPermissions()).
     */
    private function canEditRoles(): bool
    {
        $user = auth()->user();

        return $user->hasRole('ADM') || $user->can('master.credential.role.edit');
    }
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

        $allowedModules = self::ALLOWED_MODULES;

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
        if (!$this->canEditRoles()) {
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
        if (!$this->canEditRoles()) {
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
        if (!$this->canEditRoles()) {
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
        if (!$this->canEditRoles()) {
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
        if (!$this->canEditRoles()) {
            abort(403);
        }

        $role = Role::findOrFail($id);

        $allowedModules = self::ALLOWED_MODULES;

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
        if (!$this->canEditRoles()) {
            abort(403);
        }

        $role = Role::findOrFail($id);

        $role->syncPermissions([]);

        // Guard against admin lockout: revoking everything from the ADM role
        // must never strip the permission that gates this editor itself.
        if ($role->name === 'ADM') {
            $masterPerm = Permission::where('name', 'master.credential.role.edit')->first();
            if ($masterPerm) {
                $role->givePermissionTo($masterPerm);
            }
        }

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
        if (!$this->canEditRoles()) {
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
        if (!$this->canEditRoles()) {
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
        if (!$this->canEditRoles()) {
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

    /**
     * Menu tree of the editable modules, for the cascading Add Menu form.
     */
    public function menuTree()
    {
        if (!$this->canEditRoles()) {
            abort(403);
        }

        $rows = DB::table('menu_lists')
            ->whereIn('Modules', self::ALLOWED_MODULES)
            ->orderBy('Position')
            ->orderBy('Urutan')
            ->get(['ID', 'Menu_ID', 'Name', 'Parent_Menu', 'Position', 'Modules']);

        return response()->json(['success' => true, 'data' => $rows]);
    }

    /**
     * Next sequential code for a level prefix (MS / SM / SS / SI).
     * Codes are level-based and shared across modules: SM00067, SS00096, SI00391...
     */
    private function generateMenuCode(string $prefix): string
    {
        $max = DB::table('menu_lists')
            ->where('Menu_ID', 'like', $prefix.'%')
            ->selectRaw('MAX(CAST(RIGHT(Menu_ID, LENGTH(Menu_ID) - 2) AS UNSIGNED)) AS mx')
            ->value('mx');

        return $prefix . str_pad((string) (((int) $max) + 1), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Inserts one menu node and its five menu.* permissions.
     * Must be called inside a transaction.
     */
    private function insertMenuNode(string $menuId, string $name, string $parentMenu, int $position, string $module, string $pathLoc = '', int $urutan = 1)
    {
        $duplicate = DB::table('menu_lists')
            ->where('Parent_Menu', $parentMenu)
            ->where('Name', $name)
            ->exists();
        if ($duplicate) {
            throw new \InvalidArgumentException("A menu named '{$name}' already exists under this parent.");
        }

        DB::table('menu_lists')->insert([
            'Menu_ID'     => $menuId,
            'Name'        => $name,
            'Path_Loc'    => $pathLoc,
            'Modules'     => $module,
            'Position'    => $position,
            'Parent_Menu' => $parentMenu,
            'Urutan'      => $urutan,
        ]);

        foreach (self::MENU_ACTIONS as $action) {
            Permission::firstOrCreate(
                ['name' => "menu.{$menuId}.{$action}", 'guard_name' => 'web'],
                ['menu_id' => $menuId, 'action' => $action]
            );
        }

        return (object) [
            'Menu_ID'     => $menuId,
            'Name'        => $name,
            'Parent_Menu' => $parentMenu,
            'Position'    => $position,
            'Modules'     => $module,
        ];
    }

    /**
     * Guided 4-level creation: Module (MS) > Submodule (SM) > Subheader (SS,
     * optional) > Leaf item (SI). Any missing ancestor is created on the fly;
     * everything happens in one transaction.
     *
     * Payload:
     *   root_mode       existing|new      root_id | root_new
     *   submodule_mode  existing|new      submodule_id | submodule_new
     *   subheader_mode  existing|new|none subheader_id | subheader_new
     *   name (required), path_loc, urutan
     */
    public function createMenuItemCascade(Request $request)
    {
        if (!$this->canEditRoles()) {
            abort(403);
        }

        $name    = trim((string) $request->input('name'));
        $pathLoc = trim((string) $request->input('path_loc'));
        $urutan  = max(1, (int) $request->input('urutan', 1));

        if ($name === '') {
            return response()->json(['success' => false, 'message' => 'Menu name is required.']);
        }

        try {
            $chain = DB::transaction(function () use ($request, $name, $pathLoc, $urutan) {
                // ---- Level 1: module root ---------------------------------
                if (trim((string) $request->input('root_new')) !== '') {
                    $rootModule = strtoupper(trim((string) $request->input('root_module')));
                    if (!in_array($rootModule, self::ALLOWED_MODULES, true)) {
                        throw new \InvalidArgumentException('A valid module is required for a new module root.');
                    }
                    $root = $this->insertMenuNode(
                        $this->generateMenuCode('MS'),
                        trim((string) $request->input('root_new')),
                        '',
                        1,
                        $rootModule
                    );
                } else {
                    $root = DB::table('menu_lists')
                        ->where('Menu_ID', trim((string) $request->input('root_id')))
                        ->where('Position', 1)
                        ->first();
                    if (!$root) {
                        throw new \InvalidArgumentException('Selected module root was not found.');
                    }
                }
                $module = $root->Modules ?: '';

                // ---- Level 2: submodule (required) ------------------------
                if (trim((string) $request->input('submodule_new')) !== '') {
                    $submodule = $this->insertMenuNode(
                        $this->generateMenuCode('SM'),
                        trim((string) $request->input('submodule_new')),
                        $root->Menu_ID,
                        2,
                        $module,
                        '',
                        $urutan
                    );
                } else {
                    $submodule = DB::table('menu_lists')
                        ->where('Menu_ID', trim((string) $request->input('submodule_id')))
                        ->where('Position', 2)
                        ->where('Parent_Menu', $root->Menu_ID)
                        ->first();
                    if (!$submodule) {
                        throw new \InvalidArgumentException('Selected submodule was not found under the chosen module.');
                    }
                }

                // ---- Level 3: subheader (existing | new | none) ------------
                $subheader = null;
                $mode = (string) $request->input('subheader_mode', 'none');
                if ($mode === 'new') {
                    $newName = trim((string) $request->input('subheader_new'));
                    if ($newName === '') {
                        throw new \InvalidArgumentException('Subheader name is required.');
                    }
                    $subheader = $this->insertMenuNode(
                        $this->generateMenuCode('SS'),
                        $newName,
                        $submodule->Menu_ID,
                        3,
                        $module,
                        '',
                        $urutan
                    );
                } elseif ($mode === 'existing') {
                    $subheader = DB::table('menu_lists')
                        ->where('Menu_ID', trim((string) $request->input('subheader_id')))
                        ->where('Position', 3)
                        ->where('Parent_Menu', $submodule->Menu_ID)
                        ->first();
                    if (!$subheader) {
                        throw new \InvalidArgumentException('Selected subheader was not found under the chosen submodule.');
                    }
                }

                // ---- Level 4: leaf item ------------------------------------
                $leafParent = $subheader ? $subheader->Menu_ID : $submodule->Menu_ID;
                $leaf = $this->insertMenuNode(
                    $this->generateMenuCode('SI'),
                    $name,
                    $leafParent,
                    4,
                    $module,
                    $pathLoc,
                    $urutan
                );

                return [$root, $submodule, $subheader, $leaf];
            });

            [$root, $submodule, $subheader, $leaf] = $chain;

            return response()->json([
                'success' => true,
                'menu_id' => $leaf->Menu_ID,
                'chain'   => array_values(array_filter([
                    ['code' => $root->Menu_ID, 'name' => $root->Name],
                    ['code' => $submodule->Menu_ID, 'name' => $submodule->Name],
                    $subheader ? ['code' => $subheader->Menu_ID, 'name' => $subheader->Name] : null,
                    ['code' => $leaf->Menu_ID, 'name' => $leaf->Name],
                ])),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
