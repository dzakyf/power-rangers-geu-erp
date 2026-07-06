<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterWorkCalendarController extends Controller
{
    protected $Saya;
    protected $days;

    public function __construct()
    {
        $this->Saya = Auth::user()->username;
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
}
