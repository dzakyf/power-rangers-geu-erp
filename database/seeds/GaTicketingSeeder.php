<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Baseline GA ticketing master data and the GA work-order approval policy.
 *
 * Idempotent: master rows are matched by their natural code, the approval
 * policies by Policy_Code. Safe to run repeatedly.
 */
class GaTicketingSeeder extends Seeder
{
    private $now;

    public function run()
    {
        $this->now = now();
        $this->command->info('Seeding GA ticketing master data...');

        $teamIds = $this->seedTeams();
        $this->seedSlaPolicies();
        $categoryIds = $this->seedCategories($teamIds);
        $this->seedRouting($categoryIds, $teamIds);
        $this->seedQrPoints();
        $this->seedApprovalPolicies();
        $this->seedCleaningChecklists();

        $this->command->info('GaTicketingSeeder complete.');
    }

    protected function seedCleaningChecklists()
    {
        $templates = [
            'TOILET' => [
                ['Wastafel, Kran & Cermin', 'Kering, bersih bebas bercak air dan sabun'],
                ['Kloset / Urinoir & Flush', 'Disikat bersih, disinfektan, flush berfungsi normal'],
                ['Lantai & Dinding Keramik', 'Disikat, dipel kering, tidak licin dan tidak berbau'],
                ['Tempat Sampah Toilet', 'Dikosongkan, diganti plastik sampah baru'],
                ['Refill Sabun Cuci Tangan & Tissue', 'Diisi penuh dan siap pakai'],
            ],
            'OFFICE' => [
                ['Sapu & Pel Lantai Ruang Kerja', 'Lantai bersih dari debu, kotoran, dan noda'],
                ['Lap Permukaan Meja & Kursi', 'Bebas debu, rapi, dan tidak menggeser dokumen'],
                ['Kaca Pintu & Jendela', 'Dibersihkan dengan pembersih kaca'],
                ['Tempat Sampah Meja / Ruangan', 'Dikosongkan dan dibuang ke penampungan utama'],
                ['Kerapian Kabel & Area Sekitar', 'Tidak ada sampah berserakan'],
            ],
            'PANTRY' => [
                ['Bak Cuci Piring (Sink)', 'Bersih dari sisa makanan dan minyak'],
                ['Meja Counter & Meja Makan', 'Dibersihkan, disemprot sanitasi dan dilap kering'],
                ['Dispenser Air & Kulkas', 'Permukaan luar dilap bersih bebas noda'],
                ['Lantai Pantry / Kantin', 'Dipel dengan cairan pembersih lantai'],
                ['Tempat Sampah Organik & Anorganik', 'Dikosongkan dan diganti plastik baru'],
            ],
            'LOBBY' => [
                ['Lantai Utama & Keset Masuk', 'Dipel mengkilap, keset divakum / dibersihkan'],
                ['Pintu Kaca Utama & Handle', 'Bebas sidik jari dan debu'],
                ['Sofa & Meja Resepsionis', 'Dilap bersih dan rapi'],
                ['Tempat Sampah Lobby', 'Dikosongkan'],
            ],
            'CORRIDOR' => [
                ['Sapu & Pel Selasar / Koridor', 'Lantai bersih dan kering'],
                ['Dinding & Sudut Koridor', 'Bebas sarang laba-laba dan noda'],
                ['Tempat Sampah Koridor', 'Dikosongkan dan dibersihkan'],
            ],
            'OUTDOOR' => [
                ['Sapu Halaman & Area Parkir', 'Bersih dari daun kering dan sampah'],
                ['Saluran Air / Selokan Terbuka', 'Bebas sampah penyumbat'],
                ['Tempat Sampah Luar', 'Dikosongkan dan ditutup rapat'],
            ],
        ];

        foreach ($templates as $category => $items) {
            $order = 1;
            foreach ($items as $item) {
                $exists = DB::table('ga_cleaning_checklist_template')
                    ->where('Area_Category', $category)
                    ->where('Item_Name', $item[0])
                    ->first();

                if (!$exists) {
                    DB::table('ga_cleaning_checklist_template')->insert([
                        'Area_Category' => $category,
                        'Item_Name' => $item[0],
                        'Item_Description' => $item[1],
                        'Sort_Order' => $order++,
                        'Status' => 'ACTIVE',
                        'Created_At' => $this->now,
                        'Updated_At' => $this->now,
                    ]);
                }
            }
        }
    }

    protected function seedTeams()
    {
        $teams = [
            'GA-ELEC' => ['GA Electrical', 'INTERNAL'],
            'GA-PLUMB' => ['GA Plumbing', 'INTERNAL'],
            'GA-CIVIL' => ['GA Civil', 'INTERNAL'],
            'GA-HVAC' => ['GA HVAC', 'INTERNAL'],
            'GA-CLEAN' => ['GA Cleaning', 'INTERNAL'],
            'GA-SEC' => ['GA Security', 'INTERNAL'],
        ];

        $ids = [];
        foreach ($teams as $code => $team) {
            $row = DB::table('ga_team')->where('Code', $code)->first();
            if ($row) {
                $ids[$code] = (int) $row->ID;
                continue;
            }

            $ids[$code] = (int) DB::table('ga_team')->insertGetId([
                'Location' => null,
                'Company_ID' => null,
                'Code' => $code,
                'Name' => $team[0],
                'Team_Type' => $team[1],
                'Supplier_ID' => null,
                'Department' => 'HCGA',
                'Status' => 'ACTIVE',
                'Created_By' => 'seeder',
                'Created_At' => $this->now,
            ]);
        }

        $members = DB::table('users')->whereIn('username', ['user.hcga', 'manager.hcga', 'admin.hris'])->pluck('id')->all();
        foreach ($ids as $teamId) {
            foreach ($members as $userId) {
                DB::table('ga_team_member')->updateOrInsert(
                    ['Team_ID' => $teamId, 'User_Id' => (int) $userId],
                    [
                        'Supplier_ID' => null,
                        'Role_In_Team' => 'MEMBER',
                        'Status' => 'ACTIVE',
                        'Created_At' => $this->now,
                    ]
                );
            }
        }

        return $ids;
    }

    protected function seedSlaPolicies()
    {
        $policies = [
            'SLA-URGENT' => ['Urgent (15 menit / 2 jam)', 'URGENT', 15, 120],
            'SLA-HIGH' => ['High (30 menit / 4 jam)', 'HIGH', 30, 240],
            'SLA-NORMAL' => ['Normal (1 jam / 8 jam)', 'NORMAL', 60, 480],
            'SLA-LOW' => ['Low (4 jam / 24 jam)', 'LOW', 240, 1440],
        ];

        foreach ($policies as $code => $policy) {
            if (DB::table('ga_sla_policy')->where('Code', $code)->exists()) {
                continue;
            }
            DB::table('ga_sla_policy')->insert([
                'Location' => null,
                'Company_ID' => null,
                'Code' => $code,
                'Name' => $policy[0],
                'Category_ID' => null,
                'Priority' => $policy[1],
                'Response_Minutes' => $policy[2],
                'Resolve_Minutes' => $policy[3],
                'Status' => 'ACTIVE',
                'Created_By' => 'seeder',
                'Created_At' => $this->now,
            ]);
        }
    }

    protected function seedCategories(array $teamIds)
    {
        $roots = [
            'PLUMBING' => ['Plumbing & Sanitasi', 'NORMAL', 'GA-PLUMB'],
            'ELECTRICAL' => ['Kelistrikan', 'HIGH', 'GA-ELEC'],
            'HVAC' => ['AC & Ventilasi', 'HIGH', 'GA-HVAC'],
            'CIVIL' => ['Bangunan & Sipil', 'NORMAL', 'GA-CIVIL'],
            'FURNITURE' => ['Furniture & Interior', 'LOW', 'GA-CIVIL'],
            'CLEANING' => ['Kebersihan', 'NORMAL', 'GA-CLEAN'],
            'SECURITY' => ['Keamanan', 'HIGH', 'GA-SEC'],
            'OTHER' => ['Lain-lain', 'NORMAL', 'GA-CIVIL'],
        ];

        $ids = [];
        foreach ($roots as $code => $category) {
            $ids[$code] = $this->upsertCategory($code, $category[0], null, $category[1], $teamIds[$category[2]] ?? null);
        }

        $children = [
            'PLUMBING-LEAK' => ['Kebocoran Air', 'PLUMBING', 'HIGH', 'GA-PLUMB'],
            'PLUMBING-CLOG' => ['Saluran Tersumbat', 'PLUMBING', 'NORMAL', 'GA-PLUMB'],
            'ELECTRICAL-LIGHT' => ['Lampu Mati', 'ELECTRICAL', 'NORMAL', 'GA-ELEC'],
            'ELECTRICAL-POWER' => ['Daya Listrik', 'ELECTRICAL', 'URGENT', 'GA-ELEC'],
            'HVAC-AC' => ['AC Tidak Dingin', 'HVAC', 'HIGH', 'GA-HVAC'],
            'CIVIL-DOOR' => ['Pintu/Jendela Rusak', 'CIVIL', 'NORMAL', 'GA-CIVIL'],
            'FURNITURE-DESK' => ['Meja/Kursi Rusak', 'FURNITURE', 'LOW', 'GA-CIVIL'],
            'CLEANING-SPILL' => ['Tumpahan/Kotor', 'CLEANING', 'NORMAL', 'GA-CLEAN'],
            'SECURITY-INCIDENT' => ['Insiden Keamanan', 'SECURITY', 'URGENT', 'GA-SEC'],
        ];

        foreach ($children as $code => $category) {
            $ids[$code] = $this->upsertCategory(
                $code,
                $category[0],
                $ids[$category[1]] ?? null,
                $category[2],
                $teamIds[$category[3]] ?? null
            );
        }

        return $ids;
    }

    protected function upsertCategory($code, $name, $parentId, $priority, $teamId)
    {
        $row = DB::table('ga_ticket_category')->where('Code', $code)->first();
        if ($row) {
            return (int) $row->ID;
        }

        return (int) DB::table('ga_ticket_category')->insertGetId([
            'Location' => null,
            'Company_ID' => null,
            'Parent_ID' => $parentId,
            'Code' => $code,
            'Name' => $name,
            'Default_Priority' => $priority,
            'Default_Team_ID' => $teamId,
            'Sla_Policy_ID' => null,
            'Status' => 'ACTIVE',
            'Created_By' => 'seeder',
            'Created_At' => $this->now,
        ]);
    }

    protected function seedRouting(array $categoryIds, array $teamIds)
    {
        $routes = [
            'PLUMBING' => 'GA-PLUMB',
            'ELECTRICAL' => 'GA-ELEC',
            'HVAC' => 'GA-HVAC',
            'CIVIL' => 'GA-CIVIL',
            'FURNITURE' => 'GA-CIVIL',
            'CLEANING' => 'GA-CLEAN',
            'SECURITY' => 'GA-SEC',
            'OTHER' => 'GA-CIVIL',
        ];

        $sort = 1;
        foreach ($routes as $categoryCode => $teamCode) {
            if (empty($categoryIds[$categoryCode]) || empty($teamIds[$teamCode])) {
                continue;
            }

            $exists = DB::table('ga_routing_rule')
                ->where('Category_ID', $categoryIds[$categoryCode])
                ->whereNull('Subcategory_ID')
                ->whereNull('Priority')
                ->exists();
            if (!$exists) {
                DB::table('ga_routing_rule')->insert([
                    'Location' => null,
                    'Company_ID' => null,
                    'Category_ID' => $categoryIds[$categoryCode],
                    'Subcategory_ID' => null,
                    'Priority' => null,
                    'Team_ID' => $teamIds[$teamCode],
                    'Sort_Order' => $sort,
                    'Is_Active' => 1,
                    'Created_By' => 'seeder',
                    'Created_At' => $this->now,
                ]);
            }
            $sort++;
        }

        $childRoutes = [
            'PLUMBING-LEAK' => 'GA-PLUMB',
            'PLUMBING-CLOG' => 'GA-PLUMB',
            'ELECTRICAL-LIGHT' => 'GA-ELEC',
            'ELECTRICAL-POWER' => 'GA-ELEC',
            'HVAC-AC' => 'GA-HVAC',
            'CIVIL-DOOR' => 'GA-CIVIL',
            'FURNITURE-DESK' => 'GA-CIVIL',
            'CLEANING-SPILL' => 'GA-CLEAN',
            'SECURITY-INCIDENT' => 'GA-SEC',
        ];

        $sort = 100;
        foreach ($childRoutes as $subCode => $teamCode) {
            if (empty($categoryIds[$subCode]) || empty($teamIds[$teamCode])) {
                continue;
            }

            $parentCode = strpos($subCode, '-') !== false ? substr($subCode, 0, strpos($subCode, '-')) : null;
            $parentId = $parentCode && !empty($categoryIds[$parentCode]) ? $categoryIds[$parentCode] : null;

            $exists = DB::table('ga_routing_rule')
                ->where('Subcategory_ID', $categoryIds[$subCode])
                ->exists();
            if (!$exists) {
                DB::table('ga_routing_rule')->insert([
                    'Location' => null,
                    'Company_ID' => null,
                    'Category_ID' => $parentId ?: $categoryIds[$subCode],
                    'Subcategory_ID' => $categoryIds[$subCode],
                    'Priority' => null,
                    'Team_ID' => $teamIds[$teamCode],
                    'Sort_Order' => $sort,
                    'Is_Active' => 1,
                    'Created_By' => 'seeder',
                    'Created_At' => $this->now,
                ]);
            }
            $sort++;
        }
    }

    protected function seedQrPoints()
    {
        $points = [
            'GA-HO-LT1-TOILET' => ['Toilet Lantai 1', 'LOCATION', 'Head Office', '1', 'Toilet'],
            'GA-HO-LT1-LOBBY' => ['Lobby Lantai 1', 'LOCATION', 'Head Office', '1', 'Lobby'],
            'GA-HO-LT2-MEETING' => ['Ruang Meeting Lantai 2', 'ROOM', 'Head Office', '2', 'Meeting'],
            'GA-HO-LT2-PANTRY' => ['Pantry Lantai 2', 'ROOM', 'Head Office', '2', 'Pantry'],
            'GA-HO-LT3-SERVER' => ['Ruang Server Lantai 3', 'ROOM', 'Head Office', '3', 'Server'],
        ];

        foreach ($points as $code => $point) {
            if (DB::table('ga_qr_point')->where('QR_Code', $code)->exists()) {
                continue;
            }

            DB::table('ga_qr_point')->insert([
                'Location' => null,
                'Company_ID' => null,
                'QR_Code' => $code,
                'Point_Type' => $point[1],
                'Reference_Type' => null,
                'Reference_ID' => null,
                'Name' => $point[0],
                'Building' => $point[2],
                'Floor' => $point[3],
                'Room' => $point[4],
                'Status' => 'ACTIVE',
                'Created_By' => 'seeder',
                'Created_At' => $this->now,
            ]);
        }
    }

    protected function seedApprovalPolicies()
    {
        $this->createPolicy(
            'GA-WO-LOW',
            'GA Work Order <= 2.500.000 (Manager Departemen)',
            null,
            2500000.00,
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Position_Code' => null, 'Department' => null, 'Label' => 'Manager departemen pemohon'],
            ]
        );

        $this->createPolicy(
            'GA-WO-HIGH',
            'GA Work Order > 2.500.000 (Manager Departemen + VP Director)',
            2500000.01,
            null,
            [
                ['Stage_Order' => 1, 'Level_Number' => 3, 'Position_Code' => null, 'Department' => null, 'Label' => 'Manager departemen pemohon'],
                ['Stage_Order' => 2, 'Level_Number' => 1, 'Position_Code' => 'VPD', 'Department' => null, 'Label' => 'Vice President Director'],
            ]
        );
    }

    protected function createPolicy($code, $name, $minAmount, $maxAmount, array $steps)
    {
        if (DB::table('approval_policy')->where('Policy_Code', $code)->exists()) {
            return;
        }

        $policyId = (int) DB::table('approval_policy')->insertGetId([
            'Policy_Code' => $code,
            'Policy_Name' => $name,
            'Request_Type' => 'GA_WORK_ORDER',
            'Location' => null,
            'Company_ID' => null,
            'Department' => null,
            'Min_Amount' => $minAmount,
            'Max_Amount' => $maxAmount,
            'Conditions' => null,
            'Rejection_Mode' => 'RETURN_TO_CREATOR',
            'Rejection_Quorum' => null,
            'On_No_Higher_Level' => 'AUTO_APPROVE',
            'On_Vacancy' => 'FALLBACK',
            'Allow_Self_Approval' => 0,
            'Allow_Delegation' => 1,
            'Status' => 'ACTIVE',
            'Version' => 1,
            'Effective_From' => null,
            'Effective_To' => null,
            'Created_By' => 'seeder',
            'Created_At' => $this->now,
        ]);

        foreach ($steps as $step) {
            DB::table('approval_policy_step')->insert([
                'Policy_ID' => $policyId,
                'Stage_Order' => $step['Stage_Order'],
                'Level_Number' => $step['Level_Number'],
                'Position_ID' => null,
                'Position_Code' => $step['Position_Code'],
                'Department' => $step['Department'],
                'Approval_Mode' => 'ALL',
                'Quorum' => null,
                'Is_Required' => 1,
                'Label' => $step['Label'],
                'Created_At' => $this->now,
            ]);
        }

        $this->command->info("Created GA approval policy {$code}.");
    }
}
