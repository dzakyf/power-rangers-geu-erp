<?php
$classPaths = glob(str_replace('{{1}}', '', __DIR__) . '{{2}}/*.php');
$classes = array();
$namespace = '{{3}}';
foreach ($classPaths as $classPath) {
    $segments = explode('/', $classPath);
    $segments = explode('\\', $segments[count($segments) - 1]);
    $classes[] = $namespace . $segments[count($segments) - 1];
}
echo "<pre>";
print_r($classes);
echo "</pre>";
