<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Role;
use App\Permission;
use App\User;
use App\Services\RbacService;

/**
 * GA ticketing menu entries, permissions and role grants.
 *
 * MenuListsSeeder truncates menu_lists, so this seeder must run after it.
 * Idempotent: rows and permissions are upserted, role grants are additive.
 */
class GaMenuSeeder extends Seeder
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve'];

    private const MENUS = [
        'SS00102' => ['GA Ticketing', '#', 3, 'SM00067', 10],
        'SI01009' => ['Tickets', 'hrd-ga-ticket-index', 4, 'SS00102', 1],
        'SI01010' => ['Work Orders', 'hrd-ga-work-order-index', 4, 'SS00102', 2],
        'SI01011' => ['My Assignments', 'hrd-ga-my-assignment-index', 4, 'SS00102', 3],
        'SI01012' => ['Ticket Master', 'hrd-ga-ticket-master-index', 4, 'SS00102', 4],
        'SI01013' => ['QR Points', 'hrd-ga-qr-point-index', 4, 'SS00102', 5],
        'SS00103' => ['GA Inventory', '#', 3, 'SM00067', 11],
        'SI01014' => ['Low Stock', 'hrd-ga-inventory-low-stock', 4, 'SS00103', 1],
        'SI01015' => ['Stock Ledger', 'hrd-ga-inventory-ledger', 4, 'SS00103', 2],
        'SI01016' => ['Minimum Stock', 'hrd-ga-inventory-minimum-stock', 4, 'SS00103', 3],
        'SI01017' => ['Cutoff Stock', 'hrd-ga-inventory-cutoff', 4, 'SS00103', 4],
        'SI01025' => ['YTD Stock', 'hrd-ga-inventory-ytd', 4, 'SS00103', 5],
        'SI01018' => ['Inventory Incoming', 'inventory-incoming', 4, 'SS00103', 5],
        'SI01019' => ['Inventory Request', 'inventory-request', 4, 'SS00103', 6],
        'SI01020' => ['Moving Stock', 'hrd-ga-inventory-transfer-stock', 4, 'SS00103', 7],
        'SI01021' => ['Reusable Items', 'hrd-ga-inventory-reusable', 4, 'SS00103', 8],
        'SI01022' => ['Room Booking', 'hrd-ga-room-booking-index', 4, 'SS00102', 6],
        'SI01023' => ['Security & Visitor', 'hrd-ga-security-index', 4, 'SS00102', 7],
        'SI01024' => ['Cleaning Management', 'hrd-ga-cleaning-index', 4, 'SS00102', 8],
    ];

    public function run()
    {
        $this->command->info('Seeding GA ticketing menus...');

        foreach (self::MENUS as $menuId => $menu) {
            DB::table('menu_lists')->updateOrInsert(
                ['Menu_ID' => $menuId],
                [
                    'Name' => $menu[0],
                    'Path_Loc' => $menu[1],
                    'Position' => $menu[2],
                    'Parent_Menu' => $menu[3],
                    'Urutan' => $menu[4],
                    'Modules' => 'HRD',
                ]
            );
        }

        $permissionNames = [];
        foreach (array_keys(self::MENUS) as $menuId) {
            foreach (self::ACTIONS as $action) {
                $name = "menu.{$menuId}.{$action}";
                Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['menu_id' => $menuId, 'action' => $action]
                );
                $permissionNames[] = $name;
            }
        }

        foreach (['ADM', 'HRD'] as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $existing = $role->permissions()->pluck('name')->flip();
            $missing = array_values(array_filter($permissionNames, function ($name) use ($existing) {
                return !$existing->has($name);
            }));

            if (!empty($missing)) {
                $role->givePermissionTo($missing);
                $this->command->info("Granted " . count($missing) . " GA permissions to {$roleName}.");
            }
        }

        $this->command->info('Rebuilding Sub_Menu for role holders...');
        $roleIds = Role::pluck('id');
        $userIds = DB::table('model_has_roles')->whereIn('role_id', $roleIds)->pluck('model_id')->unique();
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                RbacService::rebuildSubMenu($user);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('GaMenuSeeder complete.');
    }
}
