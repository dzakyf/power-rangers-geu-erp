<?php

$liveHost = getenv('LIVE_HOST') ?: 'host.docker.internal';
$livePort = getenv('LIVE_PORT') ?: '13306';
$liveDb   = getenv('LIVE_DB') ?: 'erp_ksps';
$liveUser = getenv('LIVE_USER') ?: 'root';
$livePass = getenv('LIVE_PASS') ?: '';

$localHost = getenv('LOCAL_HOST') ?: 'erp-test-mysql';
$localPort = getenv('LOCAL_PORT') ?: '3306';
$localDb   = getenv('LOCAL_DB') ?: 'erp_bdp_live';
$localUser = getenv('LOCAL_USER') ?: 'root';
$localPass = getenv('LOCAL_PASS') ?: '';

$pdo = function (string $host, string $port, string $db, string $user, string $pass): PDO {
    return new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
};

$live = $pdo($liveHost, $livePort, $liveDb, $liveUser, $livePass);
$local = $pdo($localHost, $localPort, $localDb, $localUser, $localPass);

function columns(PDO $pdo, string $db, string $table): array
{
    $stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND (extra NOT LIKE '%GENERATED%' OR extra IS NULL) ORDER BY ordinal_position");
    $stmt->execute([$db, $table]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function rows(PDO $pdo, string $sql, array $params = []): array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function tableExists(PDO $pdo, string $db, string $table): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = ?');
    $stmt->execute([$db, $table]);
    return (int) $stmt->fetchColumn() > 0;
}

function placeholders(array $values): string
{
    return implode(',', array_fill(0, count($values), '?'));
}

function insertRows(PDO $local, string $table, array $rows, array $localCols, array $liveCols): int
{
    if ($rows === []) {
        return 0;
    }

    $cols = array_values(array_intersect($localCols, $liveCols));

    if ($cols === []) {
        return 0;
    }

    $sql = 'INSERT IGNORE INTO `'.$table.'` (`'.implode('`,`', $cols).'`) VALUES ('
        .placeholders($cols).')';
    $stmt = $local->prepare($sql);
    $count = 0;

    foreach ($rows as $row) {
        $values = [];
        foreach ($cols as $col) {
            $values[] = $row[$col] ?? null;
        }
        $stmt->execute($values);
        $count += $stmt->rowCount();
    }

    return $count;
}

$local->exec('SET FOREIGN_KEY_CHECKS=0');
$local->beginTransaction();

$clearMaster = getenv('CLEAR_MASTER') === '1';
$masterSet = [];

$allowedMenuIds = array_column(rows($live, "
    WITH RECURSIVE tree AS (
        SELECT Menu_ID, Parent_Menu, 0 AS depth
        FROM menu_lists
        WHERE Modules IN ('MITRA','FINANCE','CREDENTIAL','CANDIDATE','MYMENU','DASHBOARD')
        UNION
        SELECT m.Menu_ID, m.Parent_Menu, t.depth + 1
        FROM menu_lists m
        JOIN tree t ON m.Menu_ID = t.Parent_Menu
        WHERE t.depth < 10
    )
    SELECT DISTINCT Menu_ID FROM tree
"), 'Menu_ID');

$menuRows = $allowedMenuIds === []
    ? []
    : rows($live, 'SELECT * FROM menu_lists WHERE Menu_ID IN ('.placeholders($allowedMenuIds).')', $allowedMenuIds);

$permissionRows = $allowedMenuIds === []
    ? rows($live, "SELECT * FROM permissions WHERE name NOT LIKE 'menu.%' AND name NOT LIKE 'cns.%'")
    : rows($live, "SELECT * FROM permissions WHERE name NOT LIKE 'cns.%' AND (menu_id IN (".placeholders($allowedMenuIds).") OR name NOT LIKE 'menu.%')", $allowedMenuIds);

$permissionIds = array_column($permissionRows, 'id');

$roleRows = $permissionIds === []
    ? rows($live, "SELECT * FROM roles WHERE name IN ('ADM','super-admin')")
    : rows($live, "SELECT * FROM roles WHERE name IN ('ADM','super-admin') OR id IN (SELECT DISTINCT role_id FROM role_has_permissions WHERE permission_id IN (".placeholders($permissionIds)."))", $permissionIds);

$roleIds = array_column($roleRows, 'id');

$userRows = rows($live, 'SELECT * FROM users');
$userIds = array_column($userRows, 'id');

$rolePermissionRows = ($roleIds === [] || $permissionIds === [])
    ? []
    : rows($live, 'SELECT * FROM role_has_permissions WHERE role_id IN ('.placeholders($roleIds).') AND permission_id IN ('.placeholders($permissionIds).')', array_merge($roleIds, $permissionIds));

$modelRoleRows = ($roleIds === [] || $userIds === [])
    ? []
    : rows($live, 'SELECT * FROM model_has_roles WHERE role_id IN ('.placeholders($roleIds).') AND model_id IN ('.placeholders($userIds).')', array_merge($roleIds, $userIds));

$modelPermissionRows = [];
if (tableExists($live, $liveDb, 'model_has_permissions') && $permissionIds !== [] && $userIds !== []) {
    $modelPermissionRows = rows($live, 'SELECT * FROM model_has_permissions WHERE permission_id IN ('.placeholders($permissionIds).') AND model_id IN ('.placeholders($userIds).')', array_merge($permissionIds, $userIds));
}

$mappingRows = rows($live, 'SELECT * FROM privilege_access_mappings');

$masterTables = [
    'master_warehouse',
    'master_warehouse_account',
    'master_warehouse_profit_loss',
    'master_stock_price',
    'master_facility',
    'master_container',
    'master_category',
    'master_packaging',
    'master_work_order_status',
    'master_department',
    'master_position',
    'organization_structure',
    'approval_level',
    'approval_policy',
    'approval_policy_step',
    'master_leads_status',
    'master_bank',
    'config_system',
    'cns_master_tanks',
];

$masterRows = [];
foreach ($masterTables as $table) {
    if (tableExists($live, $liveDb, $table) && tableExists($local, $localDb, $table)) {
        $masterRows[$table] = rows($live, 'SELECT * FROM `'.$table.'`');
    }
}

$imports = [
    ['menu_lists', $menuRows],
    ['roles', $roleRows],
    ['permissions', $permissionRows],
    ['role_has_permissions', $rolePermissionRows],
    ['users', $userRows],
    ['model_has_roles', $modelRoleRows],
    ['model_has_permissions', $modelPermissionRows],
    ['privilege_access_mappings', $mappingRows],
];

foreach ($masterRows as $table => $rows) {
    $imports[] = [$table, $rows];
    $masterSet[$table] = true;
}

foreach ($imports as [$table, $rows]) {
    if (!tableExists($local, $localDb, $table)) {
        echo "skip (missing local): {$table}\n";
        continue;
    }

    if ($clearMaster && isset($masterSet[$table])) {
        $local->exec('DELETE FROM `'.$table.'`');
    }

    $localCols = columns($local, $localDb, $table);
    $liveCols = columns($live, $liveDb, $table);
    $inserted = insertRows($local, $table, $rows, $localCols, $liveCols);

    echo sprintf("%-32s fetched=%6d inserted=%6d skipped_columns=%s\n",
        $table,
        count($rows),
        $inserted,
        implode(',', array_diff($liveCols, $localCols)) ?: '-'
    );
}

$local->commit();
$local->exec('SET FOREIGN_KEY_CHECKS=1');

echo "done\n";
