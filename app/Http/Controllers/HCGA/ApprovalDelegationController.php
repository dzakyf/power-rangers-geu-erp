<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\Approval\ApprovalAuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalDelegationController extends Controller
{
    /**
     * @var ApprovalAuthorizationService
     */
    protected $authorization;

    public function __construct(ApprovalAuthorizationService $authorization)
    {
        $this->authorization = $authorization;
    }

    public function index()
    {
        $user = Auth::user();
        $isAdmin = $this->authorization->isWildcardAdmin($user);

        $users = DB::table('users')
            ->select('id', 'username', 'Name', 'Department', 'Location', 'Company_ID')
            ->orderBy('Name', 'ASC')
            ->get();

        $positions = DB::table('master_position')
            ->where('Status', 'ACTIVE')
            ->select('ID', 'Initial', 'Position', 'Department')
            ->orderBy('Position', 'ASC')
            ->get();

        $requestTypes = DB::table('approval_policy')
            ->whereNotNull('Request_Type')
            ->where('Request_Type', '!=', '')
            ->distinct()
            ->orderBy('Request_Type', 'ASC')
            ->pluck('Request_Type');

        return view('humanresources.approval.delegation', [
            'isAdmin'      => $isAdmin,
            'currentUser'  => $user,
            'users'        => $users,
            'positions'    => $positions,
            'requestTypes' => $requestTypes,
        ]);
    }

    public function data()
    {
        $user = Auth::user();
        $isAdmin = $this->authorization->isWildcardAdmin($user);

        if (stristr($_SERVER['HTTP_ACCEPT'] ?? '', 'application/xhtml+xml')) {
            header('Content-type: application/xhtml+xml');
        } else {
            header('Content-type: text/xml');
        }
        echo "<?xml version='1.0' encoding='iso-8859-1'?>\n";

        $query = DB::table('approval_delegation as d')
            ->leftJoin('users as u1', 'u1.id', '=', 'd.Delegator_Id')
            ->leftJoin('users as u2', 'u2.id', '=', 'd.Delegate_Id')
            ->leftJoin('master_position as mp', 'mp.ID', '=', 'd.Position_ID')
            ->select(
                'd.*',
                'u1.Name as Delegator_Name',
                'u1.username as Delegator_Username',
                'u2.Name as Delegate_Name',
                'u2.username as Delegate_Username',
                'mp.Position as Position_Name',
                'mp.Initial as Position_Code'
            )
            ->orderBy('d.ID', 'DESC');

        if (!$isAdmin) {
            $uid = (int) $user->id;
            $query->where(function ($q) use ($uid) {
                $q->where('d.Delegator_Id', $uid)
                  ->orWhere('d.Delegate_Id', $uid);
            });
        }

        echo "<rows>";
        $no = 1;
        foreach ($query->get() as $row) {
            $delegator = ($row->Delegator_Name ?: $row->Delegator_Username ?: '-') . " (#{$row->Delegator_Id})";
            $delegate = ($row->Delegate_Name ?: $row->Delegate_Username ?: '-') . " (#{$row->Delegate_Id})";
            $posText = $row->Position_ID ? trim(($row->Position_Code ? $row->Position_Code . ' - ' : '') . ($row->Position_Name ?? '')) : 'Semua Jabatan';
            $period = ($row->Start_Date ?: '-') . ' s/d ' . ($row->End_Date ?: '-');

            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . $this->xml($delegator) . '</cell>';
            echo '<cell>' . $this->xml($delegate) . '</cell>';
            echo '<cell>' . $this->xml($posText) . '</cell>';
            echo '<cell>' . $this->xml($row->Request_Type ?: 'SEMUA') . '</cell>';
            echo '<cell>' . $this->xml($row->Location . ' / ' . $row->Company_ID) . '</cell>';
            echo '<cell>' . $this->xml($period) . '</cell>';
            echo '<cell>' . $this->xml($row->Status) . '</cell>';
            echo '<cell>' . $this->xml($row->Reason ?: '') . '</cell>';
            echo '<userdata name="delegator_id">' . (int) $row->Delegator_Id . '</userdata>';
            echo '<userdata name="status">' . $this->xml($row->Status) . '</userdata>';
            echo '</row>';
        }
        echo "</rows>";
    }

    public function save(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $this->authorization->isWildcardAdmin($user);

        $delegatorId = $isAdmin
            ? (int) $request->input('Delegator_Id', $user->id)
            : (int) $user->id;
        $delegateId = (int) $request->input('Delegate_Id');
        $positionId = $request->input('Position_ID') ? (int) $request->input('Position_ID') : null;
        $requestType = strtoupper(trim((string) $request->input('Request_Type', '')));
        $location = trim((string) $request->input('Location', $user->Location ?? 'GEU-HO'));
        $companyId = trim((string) $request->input('Company_ID', $user->Company_ID ?? 'GEU'));
        $startDate = trim((string) $request->input('Start_Date', ''));
        $endDate = trim((string) $request->input('End_Date', ''));
        $reason = trim((string) $request->input('Reason', ''));

        if ($delegatorId < 1 || $delegateId < 1) {
            return response()->json(['status' => 'error', 'message' => 'Delegator dan Delegate wajib dipilih.'], 422);
        }

        if ($delegatorId === $delegateId) {
            return response()->json(['status' => 'error', 'message' => 'Delegator dan Delegate tidak boleh orang yang sama.'], 422);
        }

        if ($startDate === '' || $endDate === '' || $startDate > $endDate) {
            return response()->json(['status' => 'error', 'message' => 'Rentang tanggal Start_Date dan End_Date tidak valid.'], 422);
        }

        if ($this->createsCircularDelegation($delegatorId, $delegateId, $startDate, $endDate)) {
            return response()->json(['status' => 'error', 'message' => 'Delegasi ditolak karena membentuk rantai delegasi melingkar (circular delegation).'], 422);
        }

        $now = Carbon::now();
        $id = DB::table('approval_delegation')->insertGetId([
            'Delegator_Id' => $delegatorId,
            'Delegate_Id'  => $delegateId,
            'Position_ID'  => $positionId,
            'Request_Type' => $requestType !== '' ? $requestType : null,
            'Location'     => $location !== '' ? $location : 'GEU-HO',
            'Company_ID'   => $companyId !== '' ? $companyId : 'GEU',
            'Start_Date'   => $startDate,
            'End_Date'     => $endDate,
            'Reason'       => $reason,
            'Status'       => 'ACTIVE',
            'Created_By'   => $user->username ?? (string) $user->id,
            'Created_At'   => $now,
            'Updated_At'   => $now,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Delegasi approval berhasil disimpan.',
            'id'      => $id,
        ]);
    }

    public function revoke(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $this->authorization->isWildcardAdmin($user);
        $id = (int) $request->input('ID');

        $row = DB::table('approval_delegation')->where('ID', $id)->first();
        if (!$row) {
            return response()->json(['status' => 'error', 'message' => 'Data delegasi tidak ditemukan.'], 404);
        }

        if (!$isAdmin && (int) $row->Delegator_Id !== (int) $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak berhak mencabut delegasi milik user lain.'], 403);
        }

        DB::table('approval_delegation')->where('ID', $id)->update([
            'Status'     => 'REVOKED',
            'Updated_At' => Carbon::now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Delegasi berhasil dicabut (REVOKED).',
        ]);
    }

    protected function createsCircularDelegation(int $delegatorId, int $delegateId, string $startDate, string $endDate): bool
    {
        $visited = [$delegatorId => true];
        $current = [$delegateId];
        $depth = 0;

        while (!empty($current) && $depth < 10) {
            $next = DB::table('approval_delegation')
                ->whereIn('Delegator_Id', $current)
                ->where('Status', 'ACTIVE')
                ->where(function ($q) use ($endDate) {
                    $q->whereNull('Start_Date')->orWhere('Start_Date', '<=', $endDate);
                })
                ->where(function ($q) use ($startDate) {
                    $q->whereNull('End_Date')->orWhere('End_Date', '>=', $startDate);
                })
                ->pluck('Delegate_Id')
                ->all();

            $batch = [];
            foreach ($next as $uid) {
                $uid = (int) $uid;
                if (isset($visited[$uid])) {
                    return true;
                }
                $visited[$uid] = true;
                $batch[] = $uid;
            }
            $current = $batch;
            $depth++;
        }

        return false;
    }

    protected function xml($value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
