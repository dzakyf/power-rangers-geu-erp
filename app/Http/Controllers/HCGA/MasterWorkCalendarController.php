<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\WorkCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterWorkCalendarController extends Controller
{
    protected $Saya;
    protected $days;
    protected $workCalendarService;

    public function __construct(WorkCalendarService $workCalendarService)
    {
        $this->workCalendarService = $workCalendarService;

        $this->middleware(function ($request, $next) {
            $this->Saya = Auth::user()->username ?? '';

            return $next($request);
        });
        
        $this->days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];
    }

    public function index()
    {
        return view('humanresources.master.work-calendar');
    }

    public function indexXml()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $nomor = 1;

        echo "<rows>";

        $Query = DB::table("work_calendars")->orderBy("Name","ASC")->get();

        foreach($Query as $Fetch){
                echo "<row id=\"$Fetch->ID\">";
                echo "<cell>$nomor</cell>";
                echo "<cell>".htmlspecialchars($Fetch->Name)."</cell>";
                echo "<cell>".htmlspecialchars($Fetch->Employee_Type)."</cell>";
                echo "<cell>".htmlspecialchars($Fetch->Weekly_Hours)."</cell>";
                echo "</row>";

        $nomor++;
        }

        echo "</rows>";
    }

    public function create()
    {
        return view('humanresources.master.work-calendar-new');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Name' => 'required|string|max:255',
            'Employee_Type' => 'required|string|max:255',
            'Weekly_Hours' => 'required|numeric|min:0',
        ]);

        $wc = DB::table('work_calendars')->insertGetId([
            'Name' => $request->input('Name'),
            'Employee_Type' => $request->input('Employee_Type'),
            'Weekly_Hours' => $request->input('Weekly_Hours'),
            'Created_By' => $this->Saya,
            'Created_At' => now(),
        ]);

        if ($request->input('Employee_Type') == 'OFFICE') {
            for ($i=1; $i <= 7; $i++) { 
                DB::table('work_calendar_details')->insert([
                    'Calendar_ID' => $wc,
                    'Day_Of_Week' => $this->days[$i],
                    'Is_Working' => 0,
                    'Default_Start' => null,
                    'Default_End' => null,
                    'Break_Minutes' => 0,
                    'Created_By' => $this->Saya,
                    'Created_At' => now(),
                ]);
            }
        } elseif ($request->input('Employee_Type') == 'SHIFT') {
            for ($i=1; $i <= 7; $i++) { 
                DB::table('shifts')->insert([
                    'Calendar_ID'     => $wc,
                    'Day_Of_Week'     => $this->days[$i],
                    'Is_Working'      => 0,
                    'Default_Start'   => null,
                    'Default_End'     => null,
                    'Code'            => 'SHIFT-' . $i,
                    'Name'            => 'Shift Day ' . $this->days[$i],
                    'Start_Time'      => '08:00:00',
                    'End_Time'        => '17:00:00',
                    'Break_Minutes'   => 60,
                    'Working_Minutes' => 480,
                    'Cross_Day'       => 0,
                    'Created_By'      => $this->Saya,
                    'Created_At'      => now(),
                ]);
            }
        }



        return response()->json([
            'status' => 'success',
            'message' => 'Work Calendar created successfully.',
        ]);
    }

    public function edit(Request $request)
    {
        $id = $request->input('id');
        $workCalendar = DB::table('work_calendars')->where('ID', $id)->first();

        if (!$workCalendar) {
            return response()->json([
                'status' => 'error',
                'message' => 'Work Calendar not found.',
            ], 404);
        }

        return view('humanresources.master.work-calendar-edit', compact('workCalendar'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'ID' => 'required|integer|exists:work_calendars,ID',
            'Name' => 'required|string|max:255',
            'Employee_Type' => 'required|string|max:255',
            'Weekly_Hours' => 'required|numeric|min:0',
        ]);

        DB::table('work_calendars')->where('ID', $request->input('ID'))->update([
            'Name' => $request->input('Name'),
            'Employee_Type' => $request->input('Employee_Type'),
            'Weekly_Hours' => $request->input('Weekly_Hours'),
            'Updated_By' => $this->Saya,
            'Updated_At' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Work Calendar updated successfully.',
        ]);
    }

    public function detail(Request $request)
    {
        $taskId = $request->task;
        $workCalendar = DB::table('work_calendars')->where('ID', $taskId)->first();
        if (!$workCalendar) {
            return response('<div class="alert alert-danger m-3">Work Calendar tidak ditemukan.</div>', 404);
        }

        $count = DB::table('work_calendar_details')
            ->where("Calendar_ID", $taskId)
            ->count();

        // Inisialisasi otomatis jika record 7 hari belum ada
        if ($count == 0) {
            for ($i = 1; $i <= 7; $i++) {
                DB::table('work_calendar_details')->insert([
                    'Calendar_ID'   => $taskId,
                    'Day_Of_Week'   => $this->days[$i],
                    'Is_Working'    => ($i <= 5) ? 1 : 0,
                    'Default_Start' => ($i <= 5) ? '08:00:00' : null,
                    'Default_End'   => ($i <= 5) ? '17:00:00' : null,
                    'Break_Minutes' => ($i <= 5) ? 60 : 0,
                    'Created_By'    => $this->Saya,
                    'Created_At'    => now(),
                ]);
            }
        }

        return view("humanresources.master.work-calendar-detail", compact('workCalendar'));
    }

    public function detailXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? '', "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $nomor = 1;
        echo "<rows>";

        $workCalendarDetail = DB::table('work_calendar_details')
            ->where("Calendar_ID", $request->task)
            ->get();

        foreach ($workCalendarDetail as $Fetch) {
            $defaultStart = $Fetch->Default_Start ? substr($Fetch->Default_Start, 0, 5) : '';
            $defaultEnd   = $Fetch->Default_End ? substr($Fetch->Default_End, 0, 5) : '';

            echo "<row id=\"$Fetch->ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Day_Of_Week) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Is_Working) . "</cell>";
            echo "<cell>" . htmlspecialchars($defaultStart) . "</cell>";
            echo "<cell>" . htmlspecialchars($defaultEnd) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Break_Minutes) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function shift(Request $request)
    {
        $taskId = $request->task;
        $workCalendar = DB::table('work_calendars')->where('ID', $taskId)->first();
        if (!$workCalendar) {
            return response('<div class="alert alert-danger m-3">Work Calendar tidak ditemukan.</div>', 404);
        }

        $count = DB::table('shifts')
            ->where("Calendar_ID", $taskId)
            ->count();

        // Inisialisasi otomatis jika baris shift belum ada untuk kalender ini
        if ($count == 0) {
            $masterShifts = DB::table('master_shifts')->where('status', 'ACTIVE')->get();

            if ($masterShifts->isNotEmpty()) {
                foreach ($masterShifts as $ms) {
                    DB::table('shifts')->insert([
                        'Calendar_ID'     => $taskId,
                        'Day_Of_Week'     => $ms->shift_name,
                        'Is_Working'      => $ms->is_off ? 0 : 1,
                        'Default_Start'   => $ms->start_time,
                        'Default_End'     => $ms->end_time,
                        'Code'            => $ms->shift_code,
                        'Name'            => $ms->shift_name,
                        'Start_Time'      => $ms->start_time,
                        'End_Time'        => $ms->end_time,
                        'Break_Minutes'   => $ms->break_minutes,
                        'Working_Minutes' => $ms->working_minutes,
                        'Cross_Day'       => $ms->is_cross_day,
                        'Created_By'      => $this->Saya,
                        'Created_At'      => now(),
                    ]);
                }
            } else {
                for ($i = 1; $i <= 7; $i++) {
                    DB::table('shifts')->insert([
                        'Calendar_ID'     => $taskId,
                        'Day_Of_Week'     => $this->days[$i],
                        'Is_Working'      => ($i <= 5) ? 1 : 0,
                        'Default_Start'   => ($i <= 5) ? '08:00:00' : null,
                        'Default_End'     => ($i <= 5) ? '17:00:00' : null,
                        'Code'            => 'SHIFT-' . $i,
                        'Name'            => 'Shift Day ' . $this->days[$i],
                        'Start_Time'      => '08:00:00',
                        'End_Time'        => '17:00:00',
                        'Break_Minutes'   => 60,
                        'Working_Minutes' => 480,
                        'Cross_Day'       => 0,
                        'Created_By'      => $this->Saya,
                        'Created_At'      => now(),
                    ]);
                }
            }
        }

        // Ambil juga daftar pola roster yang terkait dengan Calendar ini
        $rosterPatterns = DB::table('roster_patterns')
            ->where('calendar_id', $taskId)
            ->get();

        return view("humanresources.master.work-calendar-shift", compact('workCalendar', 'rosterPatterns'));
    }

    public function shiftXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? '', "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $nomor = 1;
        echo "<rows>";

        // Tampilkan daftar Master Shift yang aktif
        $masterShifts = DB::table('master_shifts')->orderBy('id', 'asc')->get();

        foreach ($masterShifts as $s) {
            $defaultStart = $s->start_time ? substr($s->start_time, 0, 5) : '';
            $defaultEnd   = $s->end_time ? substr($s->end_time, 0, 5) : '';

            echo "<row id=\"$s->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($s->shift_code) . "</cell>";
            echo "<cell>" . htmlspecialchars($s->shift_name) . "</cell>";
            echo "<cell>" . htmlspecialchars($defaultStart) . "</cell>";
            echo "<cell>" . htmlspecialchars($defaultEnd) . "</cell>";
            echo "<cell>" . (int) $s->break_minutes . "</cell>";
            echo "<cell>" . ($s->is_cross_day ? '1' : '0') . "</cell>";
            echo "<cell>" . ($s->is_off ? '1' : '0') . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function detailUpdate(Request $request)
    {
        header("Content-type: text/xml");
        echo ('<?xml version="1.0" encoding="iso-8859-1"?>' . "\n");

        $params = $this->parseDataProcessorInput($request);
        $actions = $this->workCalendarService->processCalendarDetailsDataProcessor($params, $this->Saya);

        echo "<data>";
        foreach ($actions as $act) {
            echo "<action type='" . $act['type'] . "' sid='" . $act['sid'] . "' tid='" . $act['tid'] . "'/>";
        }
        echo "</data>";
    }

    public function shiftUpdate(Request $request)
    {
        header("Content-type: text/xml");
        echo ('<?xml version="1.0" encoding="iso-8859-1"?>' . "\n");

        $params = $this->parseDataProcessorInput($request);
        $actions = $this->workCalendarService->processShiftsDataProcessor($params, $this->Saya);

        echo "<data>";
        foreach ($actions as $act) {
            echo "<action type='" . $act['type'] . "' sid='" . $act['sid'] . "' tid='" . $act['tid'] . "'/>";
        }
        echo "</data>";
    }

    /**
     * Membaca dan menormalisasi parameter dataProcessor dari POST form-data atau raw body.
     */
    protected function parseDataProcessorInput(Request $request): array
    {
        $params = $request->all();
        if (empty($params) || empty($params['ids'])) {
            $raw = $request->getContent();
            if (!empty($raw)) {
                parse_str(urldecode($raw), $parsed);
                if (is_array($parsed) && !empty($parsed)) {
                    $params = array_merge($params, $parsed);
                }
            }
        }
        return $params;
    }
}
