<?php

namespace App\Http\Controllers\HCGA\GA;

use App\Http\Controllers\Controller;
use App\Services\GA\GaSecurityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SecurityController extends Controller
{
    use GaXml;

    protected $security;

    public function __construct(GaSecurityService $security)
    {
        $this->security = $security;
    }

    public function index()
    {
        $locations = DB::table('ga_security_visitor')->select('Location')->distinct()->pluck('Location');
        return view('humanresources.ga.security.index', compact('locations'));
    }

    public function visitorData(Request $request)
    {
        $query = DB::table('ga_security_visitor');

        if ($request->filled('date')) {
            $query->whereDate('Time_In', $request->input('date'));
        }
        if ($request->filled('status')) {
            $query->where('Status', $request->input('status'));
        }

        $visitors = $query->orderBy('id', 'DESC')->limit(1000)->get();

        header('Content-Type: text/xml');
        echo '<rows>';
        $no = 1;
        foreach ($visitors as $v) {
            echo '<row id="' . (int) $v->id . '">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . $this->xml($v->Visitor_No) . '</cell>';
            echo '<cell>' . $this->xml($v->Visitor_Name) . '</cell>';
            echo '<cell>' . $this->xml($v->Institution ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($v->Host_Name) . '</cell>';
            echo '<cell>' . $this->xml($v->Purpose) . '</cell>';
            echo '<cell>' . ($v->Badge_Number ?: '-') . '</cell>';
            echo '<cell>' . Carbon::parse($v->Time_In)->format('Y-m-d H:i') . '</cell>';
            echo '<cell>' . ($v->Time_Out ? Carbon::parse($v->Time_Out)->format('Y-m-d H:i') : '-') . '</cell>';
            echo '<cell>' . $this->xml($v->Status) . '</cell>';
            echo '</row>';
        }
        echo '</rows>';
    }

    public function saveVisitorEntry(Request $request)
    {
        try {
            $visitor = $this->security->recordVisitorEntry($request->all(), Auth::user());
            return response()->json([
                'status' => 'success',
                'message' => 'Pengunjung berhasil dicatat masuk dengan No: ' . $visitor->Visitor_No,
                'data' => $visitor
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function saveVisitorExit(Request $request)
    {
        try {
            $id = $request->input('id');
            $visitor = $this->security->recordVisitorExit($id, $request->all(), Auth::user());
            return response()->json([
                'status' => 'success',
                'message' => 'Pengunjung ' . $visitor->Visitor_Name . ' berhasil dicatat keluar.',
                'data' => $visitor
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function incidentData(Request $request)
    {
        $query = DB::table('ga_security_incident');

        if ($request->filled('date_from')) {
            $query->where('Incident_Date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('Incident_Date', '<=', $request->input('date_to'));
        }
        if ($request->filled('status')) {
            $query->where('Status', $request->input('status'));
        }

        $user = Auth::user();
        $isSecurityHeadOrAdmin = ($user && in_array(strtoupper((string) $user->Position), ['SECURITY HEAD', 'GA HEAD', 'MANAGER HCGA', 'ADMINISTRATOR'], true));

        $incidents = $query->orderBy('id', 'DESC')->limit(500)->get();

        header('Content-Type: text/xml');
        echo '<rows>';
        $no = 1;
        foreach ($incidents as $inc) {
            // Role-based visibility check for strictly confidential
            $displayTitle = $inc->Title;
            $displayDesc = $inc->Description;

            if ($inc->Confidentiality === 'STRICTLY_CONFIDENTIAL' && !$isSecurityHeadOrAdmin) {
                $displayTitle = '[TERBATAS/RAHASIA] Insiden #' . $inc->Incident_No;
                $displayDesc = 'Rincian insiden hanya dapat diakses oleh Security Head / Head of GA.';
            }

            echo '<row id="' . (int) $inc->id . '">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . $this->xml($inc->Incident_No) . '</cell>';
            echo '<cell>' . $this->xml($displayTitle) . '</cell>';
            echo '<cell>' . $this->xml($inc->Incident_Type) . '</cell>';
            echo '<cell>' . $this->xml($inc->Severity) . '</cell>';
            echo '<cell>' . $this->xml($inc->Confidentiality) . '</cell>';
            echo '<cell>' . $this->xml($inc->Incident_Date) . ' ' . substr((string) $inc->Incident_Time, 0, 5) . '</cell>';
            echo '<cell>' . $this->xml($inc->Location) . '</cell>';
            echo '<cell>' . $this->xml($inc->Status) . '</cell>';
            echo '<cell>' . $this->xml($displayDesc) . '</cell>';
            echo '</row>';
        }
        echo '</rows>';
    }

    public function saveIncident(Request $request)
    {
        try {
            $incident = $this->security->recordIncident($request->all(), Auth::user());
            return response()->json([
                'status' => 'success',
                'message' => 'Laporan insiden berhasil dicatat dengan No: ' . $incident->Incident_No,
                'data' => $incident
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function resolveIncident(Request $request)
    {
        try {
            $id = $request->input('id');
            $incident = $this->security->resolveIncident($id, $request->all(), Auth::user());
            return response()->json([
                'status' => 'success',
                'message' => 'Insiden ' . $incident->Incident_No . ' berhasil diupdate menjadi ' . $incident->Status,
                'data' => $incident
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function createFollowUpTicket(Request $request)
    {
        try {
            $incidentId = $request->input('incident_id');
            $result = $this->security->spawnFollowUpTicket($incidentId, $request->all(), Auth::user());
            return response()->json([
                'status' => 'success',
                'message' => 'Tiket perbaikan/tindak lanjut berhasil dibuat: ' . $result['ticket_no'],
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}

