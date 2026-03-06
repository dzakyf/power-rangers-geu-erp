<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\menu;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use SimpleXMLElement;

class UserController extends Controller
{

    protected $users;
    public function __construct(UserService $u)
    {
        $this->users = $u;
    }

    public function AuthRouteAPI(Request $request)
    {
        return $request->user();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'DESC')->paginate(10);
        // dd($users);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::findOrFail($id);
        $password = !empty($request->password) ? bcrypt($request->password) : $user->password;
        $user->update([
            'username' => $request->username,
            'password' => $password
        ]);
        return redirect(route('users.index'))->with(['success' => 'User: <strong>' . $user->username . '</strong> Diperbaharui']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with(['success' => 'User: <strong>' . $user->username . '</strong> Dihapus']);
    }

    public function form()
    {
        return view('auth.register');
    }

    public function roles(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all()
            ->pluck('name');
        // dd($user);die();
        return view('users.roles', compact('user', 'roles'));
    }

    public function rolePermission(Request $request)
    {
        // dd($_GET);
        // dd($request->all());
        $role = $request->get('role');
        // $this->menuEncode($role);


        $permissions = null;
        $hasPermission = null;

        $roles = Role::all()->pluck('name');
        // dd($roles);

        if (!empty($role)) {
            $getRole = Role::findByName($role);

            $hasPermission = DB::table('role_has_permissions')
                ->select('permissions.name')
                ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                ->where('role_id', $getRole->id)->get()->pluck('name')->all();

            $permissions = Permission::all()->pluck('name');

            $premi = DB::table('permissions as p')
                ->select(DB::raw('p.name as permission_name, m.id,m.parent_id as parent'))
                ->join('menus as m', 'p.name', '=', 'm.permission')
                ->orderBy('m.order_menu')->get();

            // dd($premi);

            $menus = menu::orderBy('order_menu', 'asc')->whereNull('parent_id')->get();
            // dd($menus);
            $perm = [];
            foreach ($premi as $key => $prem) {
                $perm['parent_id'] = $premi;
            }
            $mens = menu::with('alls');
            // dd($mens);
            // dd($perm);

            $categories = menu::whereNull('parent_id')->get();
            $allCategories = menu::pluck('name', 'id')->all();

            // dd($premi);
        }
        return view('users.role_permission', compact('roles', 'permissions', 'hasPermission', 'premi', 'categories', 'allCategories'));
    }

    public function menuEncode(Request $request)
    {

        $role = $request->get('role');
        // dd($role);
        $menu = menu::orderBy('order_menu', 'asc')->get()->toArray();
        $itemsByReference = array();
        $menus = array();
        $hasPermission = [];
        if (!empty($role)) {

            $getRole = Role::findByName($role);


            $hasPermission = DB::table('role_has_permissions')
                ->select('permissions.name')
                ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                ->where('role_id', $getRole->id)->get()->pluck('name')->all();
        }
        foreach ($menu as $key => &$item) {
            $menu[$key]['text'] = $item['permission'];
            if (!empty($role)) {
                if (in_array($item['permission'], $hasPermission)) {
                    // die();
                    $menu[$key]['state'] =
                        (object) array('selected' => true);
                }
            }
            $itemsByReference[$item['id']] = &$item;
            // Children array:
            $itemsByReference[$item['id']]['children'] = array();
            // Empty data class (so that json_encode adds "data: {}" ) 
            $itemsByReference[$item['id']]['menu'] = new \stdClass();
        }

        // Set items as children of the relevant parent item.
        foreach ($menu as $key => &$item)
            if ($item['parent_id'] && isset($itemsByReference[$item['parent_id']]))
                $itemsByReference[$item['parent_id']]['children'][] = &$item;


        // Remove items that were added to parents elsewhere:
        foreach ($menu as $key => &$item) {

            $menu[$key]['id'] = $item['id'] . '_' . $item['permission'];
            $menu[$key]['li_attr'] =
                (object) array(
                    'name' => 'permission[]',
                    'value' => $item['permission']
                );

            if ($item['parent_id'] && isset($itemsByReference[$item['parent_id']]))
                unset($menu[$key]);
        }
        foreach ($menu as $key => $value) {
            array_push($menus, $value);
        }

        echo json_encode($menus);
    }

    public function addPermission(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:permissions'
        ]);
        $permission = Permission::firstOrCreate([
            'name' => $request->name
        ]);
        return redirect()->back();
    }
    public function addRoles(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:permissions'
        ]);
        $role = Role::firstOrCreate([
            'name' => $request->name
        ]);
        return redirect()->back();
    }
    public function setRolePermission(Request $request)
    {
        // var_dump($role);
        //dd($request->all());
        //$role=Role::findByName($role);
        // dd($role);
        // dd($request);
        // $yas=       
        //$role->syncPermissions($request->permission);
        // dd($yas);

        //return redirect()->back()->with(['success'=>'Permission to role saved!']);
        $roles = $request->get('role');

        //  dd($request->get('id'));
        $ids = $request->get('id');
        $ids = array_unique($ids);
        $menu = menu::whereIn('id', $ids)->get();
        // print_r($menu['role']);
        // dd($menu[]);
        // dd($menu->pluck('role','id'));
        foreach ($menu->pluck('role', 'id') as $key => $value) {
            // print_r($value);
            // dd($key);
            if ($value === null) {

                $menuTambahRole = menu::find($key);
                // dd($menuTambahRole);
                $menuTambahRole->role = $roles;
                // dd($value);
                $menuTambahRole->save();
            }
            if ($value !== null) {
                $roleLama = '';
                // dd($key);
                $menuRubah = menu::where('id', $key)->pluck('role');
                // var_dump($menuUpdate);
                // $menuUpdate=explode("-",$menuUpdate);
                foreach ($menuRubah as $kunci => $roleName) {
                    $roleLama = $roleName;
                }
                // dd($key);
                $menuUpdate = menu::find($key);
                // var_dump($roleLama);
                // var_dump($value);

                // dd($value.','.$roleLama);
                if (strpos($roleLama, $roles) !== false) {

                    // echo ' role : '.$roles.' sudah ada pada id: '.$key;
                }
                if (strpos($roleLama, $roles) === false) {
                    // $menuUpdate->role=$roleLama;
                    $menuUpdate->role = $roleLama . "," . $roles;
                }
                $menuUpdate->save();
            }
        }
        // print_r($menu);
        // dd($menu->pluck('role','id'));
        // dd($menu);
        // if ($menu->role === null) {
        //     dd($ids);
        // }
        // if ($menu->role !== null) {
        //     dd($menu->role);
        // }
        // dd($menu);
        $role = Role::findByName($roles);
        $role->syncPermissions($request->permission);
    }

    public function setRole(Request $request, $id)
    {
        $this->validate($request, [
            'role' => 'required'
        ]);
        $user = User::findOrFail($id);

        $user->syncRoles($request->role);
        return redirect('/users')->with(['success' => 'Role sudah di set']);
    }

    public function nav()
    {
        $roles = Auth::user()->getRoleNames()->toArray();

        $permissions = array_map(function ($permission) {
            return $permission['name'];
        }, Auth::user()->getAllPermissions()->toArray());


        if (Auth::user()->hasRole('super-admin')) {

            $menus = Menu::with('descendants')->whereNull('parent_id')->where(function ($q) use ($roles, $permissions) {
                // At least 1 child need to be avilable for parent menu to appear
                $q->whereHas('descendants', function ($child) use ($roles, $permissions) {
                    $child->where(function ($q) { // Menu require no role or permission
                        $q->whereNull('role')->whereNull('permission');
                    }) // User has either role or permission
                        ->orWhereIn('role', $roles)->orWhereIn('permission', $permissions);
                })->orWhere(function ($q) { // Menu require no role or permission
                    $q->whereNotNull('url')->whereNull('role')->whereNull('permission');
                }) // Admin dont want to see menu for specific role
                    ->orWhereNull('role');
            });
        } else {

            $menus = Menu::with('descendants')->whereNull('parent_id')->where(function ($q) use ($roles, $permissions) {
                // At least 1 child need to be avilable for parent menu to appear
                $q->whereHas('descendants', function ($child) use ($roles, $permissions) {
                    $child->where(function ($q) { // Menu require no role or permission
                        $q->whereNull('role')->whereNull('permission');
                    }) // User has either role or permission
                        ->orWhereIn('role', $roles)->orWhereIn('permission', $permissions);
                })
                    ->orWhere(function ($q) { // Menu require no role or permission
                        $q->whereNotNull('url')->whereNull('role')->whereNull('permission');
                    }) // User has either role or permission
                    ->orWhereIn('role', $roles)
                    ->orWhereIn('permission', $permissions);
            });
        }

        // $menus = Menu::with('descendants')->whereNull('parent_id');

        dd($menus->get()->toArray());

        return $menus->get();
    }

    public function getPartner()
    {
        $users = $this->users->getActiveMitra();
        $xml = new SimpleXMLElement('<rows></rows>');

        foreach ($users as $p) {
            $row = $xml->addChild('row');
            $row->addChild('cell', $p->id);
            $row->addChild('cell', $p->Name);
            $row->addChild('cell', $p->Address);
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
