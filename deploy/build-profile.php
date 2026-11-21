#!/usr/bin/env php
<?php

/*
|--------------------------------------------------------------------------
| Build a stripped release artifact
|--------------------------------------------------------------------------
|
| Usage: php deploy/build-profile.php <profile>
|
| Copies the working tree (minus VCS/build/vendor), removes every module
| path not enabled by the profile, pins the generated module registry, and
| writes release-manifest.json. Composer/Artisan steps are printed, not run,
| so the build stays explicit.
|
*/

$root = dirname(__DIR__);
$profiles = require __DIR__.'/profiles.php';
$modulePaths = require __DIR__.'/module-paths.php';

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $value = getenv($key);

        return $value === false ? $default : $value;
    }
}

$name = $argv[1] ?? null;

if ($name === null || !isset($profiles[$name])) {
    fwrite(STDERR, 'usage: build-profile <'.implode('|', array_keys($profiles)).">\n");
    exit(1);
}

$profile = $profiles[$name];
$out = $root.'/build/'.$name;

remove($out);
mkdir($out, 0775, true);

copyTree($root, $out);

foreach ($modulePaths as $module => $patterns) {
    if (in_array($module, $profile['modules'], true)) {
        continue;
    }

    foreach ($patterns as $pattern) {
        foreach (glob($out.'/'.$pattern) ?: [] as $path) {
            remove($path);
        }
    }
}

$config = require $root.'/config/modules.php';
$config['enabled'] = $profile['modules'];

file_put_contents(
    $out.'/config/modules.php',
    "<?php\n\nreturn ".var_export($config, true).";\n"
);

$manifest = [
    'profile' => $name,
    'version' => $profile['version'],
    'modules' => $profile['modules'],
    'commit' => trim((string) shell_exec('git -C '.escapeshellarg($root).' rev-parse HEAD 2>/dev/null')),
    'built_at' => gmdate('c'),
];

file_put_contents(
    $out.'/release-manifest.json',
    json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
);

echo "Built {$name} -> {$out}\n";
echo "Next:\n";
echo "  cd ".escapeshellarg($out)."\n";
echo "  composer install --no-dev -o\n";
echo "  php artisan config:cache route:cache view:cache\n";
echo "  php deploy/check-boundaries.php {$name} ".escapeshellarg($out)."\n";


function copyTree(string $from, string $to, string $prefix = ''): void
{
    foreach (scandir($from) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $rel = $prefix === '' ? $entry : $prefix.'/'.$entry;

        if ($prefix === '' && in_array($entry, ['.git', 'build', 'vendor', 'node_modules'], true)) {
            continue;
        }

        $src = $from.'/'.$entry;
        $dst = $to.'/'.$entry;

        if (is_dir($src)) {
            if (!is_dir($dst)) {
                mkdir($dst, 0775, true);
            }
            copyTree($src, $dst, $rel);
        } else {
            if (!is_dir(dirname($dst))) {
                mkdir(dirname($dst), 0775, true);
            }
            copy($src, $dst);
        }
    }
}

function remove(string $path): void
{
    if (is_link($path) || is_file($path)) {
        unlink($path);
        return;
    }

    if (!is_dir($path)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }

    rmdir($path);
}
