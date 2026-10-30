<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

header('Content-Type: application/json');

$positions = DB::table('master_position')
    ->where(function ($q) {
        $q->whereNull('Status')->orWhere('Status', 'ACTIVE');
    })
    ->orderBy('Level', 'ASC')
    ->orderBy('Position', 'ASC')
    ->get(['ID', 'Position', 'Initial']);

$options = [];
$options[] = "{value:'',text:'-- None (Root) --'}";
foreach ($positions as $p) {
    $text = addslashes($p->Initial . ' - ' . $p->Position);
    $options[] = "{value:'{$p->ID}',text:'{$text}'}";
}

echo "{\noptions: [\n" . implode(",\n", $options) . "\n]\n}";

