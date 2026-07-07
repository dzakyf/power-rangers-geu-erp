<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterWorkCalendaDetailController extends Controller
{
    protected $Saya;

    public function __construct()
    {
        if(!Auth::check()) return redirect('login');
        $this->Saya = Auth::user()->username;
    }

    public function index(Request $request)
    {
        $workCalendarId = $request->query('id');
        $workCalendar = DB::table('work_calendars')->where('ID', $workCalendarId)->first();

        if (!$workCalendar) {
            return redirect()->back()->with('error', 'Work Calendar not found.');
        }

        return view('humanresources.master.work-calendar-edit', compact('workCalendar'));
    }

    public function indexXml(Request $request)
    {
        $workCalendarId = $request->query('id');
        $workCalendars = DB::table('work_calendar_details')->where('Calendar_ID', $workCalendarId)->get();

        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $no = 1;
        echo "<rows>";
        foreach ($workCalendars as $key => $workCalendar) {
            echo "<row id=\"$workCalendar->ID\">";
            echo "<cell>" . htmlspecialchars($no++) . "</cell>";
            echo "<cell>" . htmlspecialchars($workCalendar->Day_Of_Week) . "</cell>";
            echo "<cell>" . htmlspecialchars($workCalendar->Is_Working) . "</cell>";
            echo "<cell>" . htmlspecialchars($workCalendar->Default_Start) . "</cell>";
            echo "<cell>" . htmlspecialchars($workCalendar->Default_End) . "</cell>";
            echo "<cell>" . htmlspecialchars($workCalendar->Break_Minutes) . "</cell>";
            echo "</row>";
        }
        echo "</rows>";
    }
}
