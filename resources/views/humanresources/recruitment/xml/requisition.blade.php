<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
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

echo "<rows>";

$Query = DB::table("requisition")->where("Location",$LSP)->where("Status","!=","CANCELED")->orderBy("ID","DESC");
if ($priv != 'HCGA' || $priv != 'ADM') {
	$Query->where("Created_By", $saya);
}
foreach($Query->get() as $Fetch){
	
		// Perhitungan Aging Days:
		// Dimulai sejak tanggal seluruh approval PTK terpenuhi (approval terakhir).
		// Dihitung hingga tanggal kebutuhan karyawan terpenuhi (karyawan mulai bekerja / Start_Date).
		// Jika belum terpenuhi / belum mulai bekerja, dihitung berjalan sampai hari ini (now).
		// Jika approval belum lengkap / belum disetujui, aging bernilai 0 atau "-".
		$approvalCompleteDate = null;

		// 1. Cek dari Workflow Engine Baru (approval_request & approval_request_task) jika ada Approval_Request_ID
		if (!empty($Fetch->Approval_Request_ID)) {
			$appReq = DB::table('approval_request')->where('ID', $Fetch->Approval_Request_ID)->first();
			if ($appReq && in_array(strtoupper((string)$appReq->Status), ['APPROVED', 'COMPLETED'])) {
				if (!empty($appReq->Completed_At)) {
					$approvalCompleteDate = $appReq->Completed_At;
				} else {
					$lastTask = DB::table('approval_request_task')
						->where('Request_ID', $Fetch->Approval_Request_ID)
						->whereIn('Status', ['APPROVED', 'AUTO_APPROVED'])
						->orderBy('Acted_At', 'DESC')
						->orderBy('ID', 'DESC')
						->first();
					$approvalCompleteDate = $lastTask->Acted_At ?? null;
				}
			}
		}

		// 2. Fallback ke Legacy requisition_approval jika belum didapat
		if (!$approvalCompleteDate) {
			$pendingApprovalCount = DB::table('requisition_approval')
				->where('Task_ID', $Fetch->Task_ID)
				->whereIn('Approved_Status', [0, 2])
				->count();

			$hasApprovedRows = DB::table('requisition_approval')
				->where('Task_ID', $Fetch->Task_ID)
				->where('Approved_Status', 1)
				->exists();

			// Jika seluruh approval terpenuhi (tidak ada yang pending dan ada baris approved, atau status PTK APPROVED)
			if (($pendingApprovalCount === 0 && $hasApprovedRows) || strtoupper((string)$Fetch->Status) === 'APPROVED') {
				$lastApproval = DB::table('requisition_approval')
					->where('Task_ID', $Fetch->Task_ID)
					->where('Approved_Status', 1)
					->whereNotNull('Approved_Date')
					->orderBy('Approved_Date', 'DESC')
					->orderBy('ID', 'DESC')
					->first();

				$approvalCompleteDate = $lastApproval->Approved_Date ?? null;
			}
		}

		// 3. Tentukan tanggal akhir pemenuhan (karyawan mulai bekerja)
		// Cek apakah kandidat yang diterima (HIRED / CLOSED) untuk PTK ini sudah memiliki Start_Date mulai bekerja
		$hiredCandidate = DB::table('recruitment')
			->where(function($q) use ($Fetch) {
				$q->where('Requisition_ID', $Fetch->Task_ID)
				  ->orWhere('Requisition_ID', 'LIKE', '%' . $Fetch->Task_ID . '%');
			})
			->whereIn('Status', ['HIRED', 'CLOSED'])
			->whereNotNull('Start_Date')
			->where('Start_Date', '!=', '0000-00-00')
			->orderBy('Start_Date', 'ASC')
			->first();

		$fulfilledDate = null;
		if ($hiredCandidate && !empty($hiredCandidate->Start_Date)) {
			$fulfilledDate = $hiredCandidate->Start_Date;
		} elseif (in_array(strtoupper((string)$Fetch->Status), ['HIRED', 'CLOSED', 'FULFILLED']) && !empty($Fetch->Start_Date) && $Fetch->Start_Date !== '0000-00-00') {
			$fulfilledDate = $Fetch->Start_Date;
		}

		// 4. Hitung jumlah hari berjalan ($hari)
		if ($approvalCompleteDate) {
			$tglApproval = strtotime(date('Y-m-d', strtotime($approvalCompleteDate)));
			if ($fulfilledDate) {
				$tglSelesai = strtotime(date('Y-m-d', strtotime($fulfilledDate)));
			} else {
				$tglSelesai = strtotime(date('Y-m-d'));
			}
			$kurang = ($tglSelesai - $tglApproval) / 86400;
			$hari = max(0, (int) round($kurang, 0));
		} else {
			$hari = 0;
		}

		$getLog = DB::table('requisition_approval')->where("Task_ID", $Fetch->Task_ID)->where("Approved_Status", '!=', 0)->orderBy('ID', "DESC")->first();

	echo "<row id=\"$Fetch->ID\">";
		echo "<cell>$nomor</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Task_ID)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Job_Position)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->PO_Desc)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Requirement)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Department)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Priority)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Qty)."</cell>";
		echo "<cell>".htmlspecialchars($hari)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Start_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->End_Date)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->PO_No)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->PIC_Production)."</cell>";
		echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
		echo "<cell>".htmlspecialchars($getLog->Approved_Note)."</cell>";
		echo "</row>";

$nomor++;
}

echo "</rows>";
?>
