<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuListsSeeder extends Seeder
{
    protected $smModuleMap = [];

    protected $orphans = [
        'SM00045' => [2, 'MS00010', 'MITRA'],
        'SS00062' => [3, 'SM00044', 'MITRA'],
        'SI00223' => [4, 'SS00061', 'MITRA'],
        'SI00312' => [4, 'SS00083', 'CNS'],
        'FI00001' => [4, 'MS00003', 'FINANCE'],
    ];

    public function __construct()
    {
        $ranges = [
            [10, 16, 'FINANCE', 'MS00003'],
            [17, 24, 'HRD', 'MS00004'],
            [43, 55, 'MITRA', 'MS00010'],
            [56, 65, 'CNS', 'MS00011'],
        ];
        foreach ($ranges as [$start, $end, $mod, $parent]) {
            for ($i = $start; $i <= $end; $i++) {
                $this->smModuleMap[sprintf('SM%05d', $i)] = [$mod, $parent];
            }
        }
        $this->smModuleMap['SM00066'] = ['CANDIDATE', 'MC00001'];
    }

    public function run()
    {
        $backup = require __DIR__ . '/data/menu_lists_backup.php';

        $this->command->info('Reconstructing post-backup menus from users.Sub_Menu...');
        $rows = $this->buildHierarchy($backup);

        $this->command->info('Filling remaining Names/Path_Locs from routes + blade breadcrumbs...');
        $this->applyRouteBreadcrumbs($rows, $backup);

        $this->command->info('Seeding ' . count($rows) . ' rows into menu_lists...');
        DB::table('menu_lists')->truncate();
        DB::table('menu_lists')->insert(array_values($rows));

        $counts = [];
        $named = 0;
        $pathed = 0;
        foreach ($rows as $r) {
            $counts[$r['Modules']] = ($counts[$r['Modules']] ?? 0) + 1;
            if ($r['Name'] !== $r['Menu_ID']) $named++;
            if ($r['Path_Loc'] !== '#') $pathed++;
        }
        foreach ($counts as $mod => $c) {
            $this->command->info("  {$mod}: {$c} rows");
        }
        $this->command->info("Named: {$named}, with Path: {$pathed}");
        $this->command->info('MenuListsSeeder complete.');
    }

    protected function buildHierarchy($backup)
    {
        $rows = [];
        $urutan = [];

        foreach ($backup as $id => $data) {
            $rows[$id] = [
                'Menu_ID' => $id,
                'Name' => $data[0],
                'Path_Loc' => $data[1],
                'Modules' => $data[2],
                'Position' => $data[3],
                'Parent_Menu' => $data[4],
                'Urutan' => $data[5],
            ];
        }

        $users = DB::table('users')
            ->whereNotNull('Sub_Menu')
            ->where('Sub_Menu', '!=', '')
            ->get()
            ->sortByDesc(function ($u) { return strlen($u->Sub_Menu); })
            ->values();

        $leafOrder = [];

        foreach ($users as $user) {
            $currentMS = null;
            $currentSM = null;
            $currentSS = null;

            foreach (explode(',', $user->Sub_Menu) as $id) {
                $id = trim($id);
                if ($id === '' || isset($rows[$id])) {
                    $this->updateContext($id, $currentMS, $currentSM, $currentSS);
                    continue;
                }

                $prefix = substr($id, 0, 2);

                if ($prefix === 'MS') {
                    $rows[$id] = [
                        'Menu_ID' => $id, 'Name' => $id, 'Path_Loc' => '#',
                        'Position' => 1, 'Parent_Menu' => '', 'Modules' => $this->moduleFor($id),
                        'Urutan' => ($urutan['ROOT'] = ($urutan['ROOT'] ?? 0) + 1),
                    ];
                    $currentMS = $id; $currentSM = null; $currentSS = null;
                    continue;
                }

                if ($prefix === 'SB') {
                    $pos = $this->sbPosition($id);
                    $parent = $pos === 2 ? 'MS00001' : ($pos === 3 ? 'SB' . $id[2] . '0001' : 'SB' . $id[2] . '1001');
                    $this->addRow($rows, $urutan, $leafOrder, $id, $pos, $parent, 'SALES');
                    $currentMS = 'MS00001';
                    continue;
                }

                if ($prefix === 'SM') {
                    [$mod, $parent] = $this->smModuleMap[$id] ?? ['PROJECT', 'MS00002'];
                    $this->addRow($rows, $urutan, $leafOrder, $id, 2, $parent, $mod);
                    $currentSM = $id; $currentSS = null;
                    continue;
                }

                if ($prefix === 'SS') {
                    $parent = $currentMS === 'CR00001' ? 'CR00001' : ($currentSM ?? $currentMS ?? 'MS00002');
                    $mod = isset($rows[$parent]) ? $rows[$parent]['Modules'] : 'PROJECT';
                    $pos = $currentMS === 'CR00001' ? 2 : 3;
                    $this->addRow($rows, $urutan, $leafOrder, $id, $pos, $parent, $mod);
                    $currentSS = $id;
                    continue;
                }

                if ($prefix === 'SI') {
                    $parent = $currentMS === 'CR00001'
                        ? 'CR00001'
                        : ($currentSS ?? $currentSM ?? $currentMS ?? 'MS00002');
                    $mod = isset($rows[$parent]) ? $rows[$parent]['Modules'] : 'PROJECT';
                    $pos = $currentMS === 'CR00001' ? 2 : 4;
                    $this->addRow($rows, $urutan, $leafOrder, $id, $pos, $parent, $mod);
                    continue;
                }

                if ($prefix === 'MC') {
                    if ($id === 'MC00001') {
                        $rows[$id] = [
                            'Menu_ID' => $id, 'Name' => 'Candidate', 'Path_Loc' => 'candidate/dashboard',
                            'Position' => 1, 'Parent_Menu' => '', 'Modules' => 'CANDIDATE',
                            'Urutan' => ($urutan['ROOT'] = ($urutan['ROOT'] ?? 0) + 1),
                        ];
                        $currentMS = $id; $currentSM = null; $currentSS = null;
                    } else {
                        $this->addRow($rows, $urutan, $leafOrder, $id, 2, 'MC00001', 'CANDIDATE');
                        $currentSM = $id; $currentSS = null;
                    }
                    continue;
                }

                if ($prefix === 'CI') {
                    $this->addRow($rows, $urutan, $leafOrder, $id, 3, 'MC00002', 'CANDIDATE');
                    continue;
                }

                if ($prefix === 'CR') {
                    if ($id === 'CR00001') {
                        $rows[$id] = [
                            'Menu_ID' => $id, 'Name' => 'Credential', 'Path_Loc' => 'home-credential',
                            'Position' => 1, 'Parent_Menu' => '', 'Modules' => 'CREDENTIAL',
                            'Urutan' => ($urutan['ROOT'] = ($urutan['ROOT'] ?? 0) + 1),
                        ];
                        $currentMS = $id; $currentSM = null; $currentSS = null;
                    } else {
                        $this->addRow($rows, $urutan, $leafOrder, $id, 3, 'SS00092', 'CREDENTIAL');
                    }
                    continue;
                }

                if ($prefix === 'MI') {
                    $parent = $currentMS === 'MS00010' ? ($currentSS ?? '') : '';
                    $this->addRow($rows, $urutan, $leafOrder, $id, 4, $parent, 'MITRA');
                    continue;
                }

                if ($prefix === 'FI') {
                    $this->addRow($rows, $urutan, $leafOrder, $id, 4, 'MS00003', 'FINANCE');
                    continue;
                }
            }
        }

        foreach ($this->orphans as $id => [$pos, $parent, $mod]) {
            if (!isset($rows[$id])) {
                $this->addRow($rows, $urutan, $leafOrder, $id, $pos, $parent, $mod);
            }
        }

        return $rows;
    }

    protected function moduleFor($id)
    {
        $map = [
            'MS00001' => 'SALES', 'MS00002' => 'PROJECT', 'MS00003' => 'FINANCE',
            'MS00004' => 'HRD', 'MS00005' => 'ACCOUNTING', 'MS00006' => 'ASET',
            'MS00007' => 'PROCUREMENT', 'MS00008' => 'MYMENU', 'MS00009' => 'DASHBOARD',
            'MS00010' => 'MITRA', 'MS00011' => 'CNS',
        ];
        return $map[$id] ?? 'PROJECT';
    }

    protected function updateContext($id, &$currentMS, &$currentSM, &$currentSS)
    {
        $prefix = substr($id, 0, 2);
        if ($prefix === 'MS' || $id === 'CR00001' || $id === 'MC00001') {
            $currentMS = $id; $currentSM = null; $currentSS = null;
        } elseif ($prefix === 'SM') {
            $currentSM = $id; $currentSS = null;
        } elseif ($prefix === 'SS') {
            $currentSS = $id;
        }
    }

    protected function sbPosition($id)
    {
        $d2 = $id[3];
        $d3 = $id[4];
        if ($d2 === '0') {
            return 2;
        }
        return $d3 === '0' ? 3 : 4;
    }

    protected function addRow(&$rows, &$urutan, &$leafOrder, $id, $pos, $parent, $mod)
    {
        $key = $parent === '' ? 'ROOT' : $parent;
        if (!isset($urutan[$key])) {
            $max = 0;
            foreach ($rows as $r) {
                if ($r['Parent_Menu'] === $parent && $r['Urutan'] > $max) {
                    $max = $r['Urutan'];
                }
            }
            $urutan[$key] = $max;
        }
        $n = $urutan[$key] + 1;
        $urutan[$key] = $n;
        $rows[$id] = [
            'Menu_ID' => $id,
            'Name' => $id,
            'Path_Loc' => '#',
            'Position' => $pos,
            'Parent_Menu' => $parent,
            'Urutan' => $n,
            'Modules' => $mod,
        ];
        if ($pos === 4) {
            $leafOrder[$mod][] = $id;
        }
    }

    protected function applyRouteBreadcrumbs(&$rows, $backup)
    {
        $viewModules = [
            'humanresources' => 'HRD',
            'finance' => 'FINANCE',
            'accounting' => 'ACCOUNTING',
            'sales' => 'SALES',
            'project' => 'PROJECT',
            'procurement' => 'PROCUREMENT',
            'assets' => 'ASET',
            'partner' => 'MITRA',
            'mymenu' => 'MYMENU',
            'credential' => 'CREDENTIAL',
            'dashboard' => 'DASHBOARD',
            'candidate' => 'CANDIDATE',
            'uco' => 'CNS',
        ];

        $routes = $this->extractClosureRoutes();
        $routePools = [];
        foreach ($routes as $route) {
            $mod = $viewModules[$route['module']] ?? null;
            if ($mod === null || !$route['l2']) {
                continue;
            }
            $routePools[$mod][] = $route;
        }

        $moduleLeaves = [];
        foreach ($rows as $r) {
            if ($r['Position'] === 4) {
                $moduleLeaves[$r['Modules']][] = $r['Menu_ID'];
            }
        }

        $leafRoute = [];
        foreach ($moduleLeaves as $mod => $leaves) {
            $pool = $routePools[$mod] ?? [];
            $poolIdx = 0;
            foreach ($leaves as $leafId) {
                if (isset($backup[$leafId])) {
                    continue;
                }
                if (!isset($pool[$poolIdx])) {
                    break;
                }
                $rows[$leafId]['Name'] = $pool[$poolIdx]['l2'];
                $rows[$leafId]['Path_Loc'] = $pool[$poolIdx]['path'];
                $leafRoute[$leafId] = $pool[$poolIdx];
                $poolIdx++;
            }
        }

        for ($pass = 0; $pass < 2; $pass++) {
            foreach ($rows as $id => $r) {
                if ($r['Position'] !== 3 && $r['Position'] !== 2) {
                    continue;
                }
                if ($r['Name'] !== $id) {
                    continue;
                }
                $children = array_filter($moduleLeaves[$r['Modules']] ?? [], function ($leaf) use ($rows, $id) {
                    return ($rows[$leaf]['Parent_Menu'] ?? '') === $id;
                });
                foreach ($children as $child) {
                    if (isset($leafRoute[$child])) {
                        $rows[$id]['Name'] = $leafRoute[$child]['l1'] ?: $rows[$id]['Name'];
                        break;
                    }
                }
                if ($rows[$id]['Name'] === $id && $r['Position'] === 2) {
                    $subs = array_filter($rows, function ($s) use ($id) {
                        return $s['Position'] === 3 && $s['Parent_Menu'] === $id && $s['Name'] !== $s['Menu_ID'];
                    });
                    foreach ($subs as $sub) {
                        $rows[$id]['Name'] = $sub['Name'];
                        break;
                    }
                }
            }
        }
    }

    protected function extractClosureRoutes()
    {
        $web = file_get_contents(base_path('routes/web.php'));
        $routes = [];

        preg_match_all(
            '/Route::get\(\s*([\'"])([^\'"]+)\1\s*,\s*function\s*\([^)]*\)\s*\{.*?return\s+view\(\s*([\'"])([^\'"]+)\3\s*\)/s',
            $web,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $m) {
            $path = $m[2];
            $view = $m[4];
            $blade = base_path('resources/views/' . str_replace('.', '/', $view) . '.blade.php');
            if (!is_file($blade)) {
                continue;
            }
            $content = file_get_contents($blade);
            $bc = $this->extractBreadcrumb($content);
            if ($bc === null) {
                continue;
            }
            $routes[] = [
                'path' => ltrim($path, '/'),
                'module' => explode('.', $view)[0],
                'l1' => $bc['l1'],
                'l2' => $bc['l2'],
            ];
        }

        return $routes;
    }

    protected function extractBreadcrumb($content)
    {
        if (!preg_match('/<div class="main-content-header">(.*?)<ol class="breadcrumb">(.*?)<\/ol>/s', $content, $m)) {
            return null;
        }
        preg_match('/<h1>(.*?)<\/h1>/s', $m[1], $h);
        preg_match('/<span class="active">(.*?)<\/span>/s', $m[2], $active);
        preg_match('/<li class="breadcrumb-item">\s*(?:<a[^>]*>)?(.*?)(?:<\/a>)?\s*<\/li>/s', $m[2], $l1);

        $clean = function ($s) { return trim(strip_tags(html_entity_decode($s))); };

        return [
            'h1' => isset($h[1]) ? $clean($h[1]) : '',
            'l1' => isset($l1[1]) ? $clean($l1[1]) : '',
            'l2' => isset($active[1]) ? $clean($active[1]) : '',
        ];
    }
}
