<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Ticket;
use App\TicketLog;
use App\TicketResponse;
use App\TicketResponseLog;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    protected $Saya;
    protected $dt;

    public function __construct()
    {
        $this->dt = \Carbon\Carbon::now();
    }

    public function ticketNew()
    {
        return view('partner.ticket-new');
    }

    public function ticketNewSubmit(Request $request)
    {
        $Saya = auth()->user()->username;
        $dt = \Carbon\Carbon::now();
        
        $arr = $request->all();
        unset($arr['_token']);

        $arr['Created_By'] = $Saya;
        $arr['Created_Date'] = $dt;
        $arr['Ticket_Status'] = "OPEN";

       $ticket = Ticket::create($arr);

       $arr['Action_By'] = $Saya;
       $arr['Action_Date'] = $dt;
       $arr['Action_Status'] = "CREATE_TICKET_PARTNER";
       $arr['Last_ID'] = $ticket->ID;

       TicketLog::create($arr);

        echo "success";
    }

    public function ticketOpen()
    {
        return view('partner.ticket-open');
    }

    public function ticketOpenXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
        }
        $nomor = 1;
        $get = Ticket::where("Ticket_Status", '!=', 'CLOSED');
        if ($priv == 'PARTNER') {
            $get->where("Created_By", auth()->user()->username);
        }

        $get = $get->get();
        
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Ticket_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Ticket_Status) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Proposed_Date) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Ticket_Type) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Ticket_Category) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Ticket_Subject) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Description) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->File_Document) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";

    }

    public function ticketOpenDetail()
    {
        return view('partner.ticket-open-detail');
    }

    public function ticketOpenDetailXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $nomor = 1;
        $get = TicketResponse::where("Ticket_No", $request->Task_ID)->get();
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Ticket_Response) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Status_Progress) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Completion_Note) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function ticketOpenDetailSubmit(Request $request)
    {
        $this->Saya = Auth::user()->username;
        try {
            $arr = $request->all();
    
            $arr['Created_By'] = $this->Saya;
            $arr['Created_Date'] = $this->dt;
    
            $tr = TicketResponse::create($arr);
    
            if ($arr['Status_Progress'] == 'CLOSED') {
                $tt = Ticket::where("Ticket_No", $request->Ticket_No)->first();
                if ($tt) {
                    Ticket::where("ID", $tt->ID)->update([
                        'Ticket_Status' => $arr['Status_Progress'],
                        'Completion_Note' => $arr['Completion_Note'],
                    ]);
                }
            }
    
            $arr['Action_By'] = $this->Saya;
            $arr['Action_Status'] = $this->dt;
            $arr['Action_Status'] = "CREATE_TICKET_RESPONS";
            $arr['Last_ID'] = $tr->ID;
    
            TicketResponseLog::create($arr);
    
            echo "success";
        } catch (\Throwable $th) {
            Log::error($th);
            echo $th->getMessage();
        }
    }

    public function ticketHistory()
    {
        return view('partner.ticket-history');
    }

    public function ticketHistoryXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
        }
        $nomor = 1;
        $get = Ticket::where("Ticket_Status", 'CLOSED')->get();
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Ticket_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Ticket_Status) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Proposed_Date) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Ticket_Type) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Ticket_Category) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Ticket_Subject) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Description) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->File_Document) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";

    }

    public function ticketHistoryDetail()
    {
        return view('partner.ticket-history-detail');
    }

    public function ticketHistoryDetailXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $nomor = 1;
        $get = TicketResponse::where("Ticket_No", $request->Task_ID)->get();
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Ticket_Response) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Status_Progress) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Completion_Note) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }
}
