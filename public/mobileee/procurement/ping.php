<?php
require_once __DIR__ . '/app_guard.php';
appGuard(true);
header('Cache-Control: no-store');
header('Content-Type: text/plain');
echo 'ok';