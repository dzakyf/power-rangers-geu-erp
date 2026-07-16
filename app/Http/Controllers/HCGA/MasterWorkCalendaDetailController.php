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

    public function update()
    {
        header("Content-type: text/xml");
        echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 

        echo "<data>";

        $request_body = urldecode(file_get_contents('php://input'));
        $Saya = Auth::user()->username;
        $LSP = Auth::user()->Location;

        $ids = explode("&",$request_body);
        foreach($ids as $datax){
            
            $idenya = explode("=",$datax);
            $param[$idenya[0]] = $idenya[1];
            
        }
        $checkids = explode(",",$param["ids"]);

        for ($i=0; $i < sizeof($checkids); $i++) { 
            $rowId = $checkids[$i]; //id or row which was updated 
            $newId = $rowId; //will be used for insert operation	
            $mode = $param[$rowId."_!nativeeditor_status"]; 
                $d = $param[$rowId."_c4"]; // transport
                $a = $param[$rowId."_c1"]; // origin
                switch($mode){
                    case "updated":
                        $action = updatekan($rowId,$param);break;
                case "deleted":
                        $action = deletekan($rowId,$param);break;
                case "inserted":
                        $action = insertkan($rowId,$param);break;
                }
                
            echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
        }
        echo "</data>";
    }
    


    function updatekan($rowId,$param) {
        
        $a = $param[$rowId."_c1"]; // origin
        $b = $param[$rowId."_c2"]; // destination
        $c = $param[$rowId."_c3"]; // destination name
        $d = $param[$rowId."_c4"]; // transport
        $e = $param[$rowId."_c5"]; // service
        $f = $param[$rowId."_c6"]; // treshold
        $g = $param[$rowId."_c7"]; // unit price
        $h = $param[$rowId."_c8"]; // additional
        $ix = $param[$rowId."_c9"]; // lead time
        $j = $param[$rowId."_c10"]; // lead time
        $k = $param[$rowId."_c11"]; // lead time
        $l = $param[$rowId."_c12"]; // lead time
        $m = $param[$rowId."_c13"]; // lead time
        $n = $param[$rowId."_c14"]; // lead time
        $o = $param[$rowId."_c15"]; // lead time
        $p = $param[$rowId."_c16"]; // lead time

        $Saya = Auth::user()->username;
        $LSP = Auth::user()->Location;

        $upd = [];
        if ($c != "") {
            $upd[] = "Default_Start='$c'";
        }
        if ($d != "") {
            $upd[] = "Default_End='$c'";
        }
        if ($f != 0) {
            $upd[] = "Break_Minutes='$c'";
        }

        if(count($upd) > 0) {
            $upd = implode(', ', $upd);
            $upd = ", " . $upd;
        }else {
            $upd = "";
        }


        DB::update(DB::raw("UPDATE work_calendar_details set Is_Working = '$b' $upd  where ID=$rowId"));
        
        return "update";
    }

    function deletekan($rowId,$param){
        DB::delete(DB::raw("DELETE FROM work_calendar_details where ID=$rowId")); 	
    }
    
    function insertkan($rowId,$param){
        $a = $param[$rowId."_c1"]; // origin
        $b = $param[$rowId."_c2"]; // destination
        $c = $param[$rowId."_c3"]; // destination name
        $d = $param[$rowId."_c4"]; // transport
        $e = $param[$rowId."_c5"]; // service
        $f = $param[$rowId."_c6"]; // treshold
        $g = $param[$rowId."_c7"]; // unit price
        $h = $param[$rowId."_c8"]; // additional
        $ix = $param[$rowId."_c9"]; // lead time
        $j = $param[$rowId."_c10"]; // lead time
        $k = $param[$rowId."_c11"]; // lead time
        $l = $param[$rowId."_c12"]; // lead time
        $m = $param[$rowId."_c13"]; // lead time
        $n = $param[$rowId."_c14"]; // lead time
        $o = $param[$rowId."_c15"]; // lead time
        $p = $param[$rowId."_c16"]; // lead time

        $Saya = Auth::user()->username;
        $LSP = Auth::user()->Location;

        DB::insert(DB::raw("INSERT INTO work_calendar_details(Directorat,Department,Initial_Code,Initial,Created_By,Created_Date) VALUES('$a','$b','$c','$d','$Saya',NOW())"));


        
        
        return "insert";
        
    }
}
