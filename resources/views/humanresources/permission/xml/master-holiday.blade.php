<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
  		header("Content-type: application/xhtml+xml"); } else {
  		header("Content-type: text/xml");
	}
	echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");



$LSP = Auth::user()->Location;
$DOP = Auth::user()->DOP;
$priv = Auth::user()->Priv_ID;
$saya = Auth::user()->username;
$nomor = 1;
$url = 'https://api-hari-libur.vercel.app/api?year=' . date("Y");

// 1. Initialize cURL session
$ch = curl_init();

// 2. Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
curl_setopt($ch, CURLOPT_TIMEOUT, 10);          // Set a timeout threshold
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
]);

// 3. Execute request and catch response
$response = curl_exec($ch);

// 4. Handle errors if any exist
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    curl_close($ch);
    return response()->json(['error' => $error_msg], 500);
}

// 5. Close session and parse JSON data
curl_close($ch);
$data = json_decode($response, true);

$counter = DB::table('libur')->where('libur_tgl', "LIKE", date("Y").'%')->count();
if (count($data['data']) != $counter) {
	foreach ($data['data'] as $holiday) {
		DB::table('libur')->updateOrInsert(
			['libur_tgl' => $holiday['date']],
			[
				'libur_status' => Str::contains($holiday['description'], 'cuti') ? 2 : 1,
				'libur_keterangan' => $holiday['description'],
			]
		);
	}
}

echo "<rows>";

$Query = DB::table("libur")->orderBy("libur_tgl","DESC")->get();

foreach($Query as $Fetch){
		echo "<row id=\"$Fetch->libur_tgl\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars(date("Y",strtotime($Fetch->libur_tgl)))."</cell>";
		echo "<cell>".htmlspecialchars(date("D",strtotime($Fetch->libur_tgl)))."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->libur_tgl)."</cell>";
		echo "<cell>".htmlspecialchars(($Fetch->libur_status == 1 ) ? "Hari Libur" : "Cuti Bersama" )."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->libur_keterangan)."</cell>";
		echo "</row>";

$nomor++;
}

echo "</rows>";
?>
