<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterApprovalController extends Controller
{
    public function index()
    {
        return view('humanresources.master.approval');
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

        $getDirectorat = DB::table("master_directorat")->orderBy("Directorat","ASC")->get();
        foreach($getDirectorat as $FetchDirectorat){
            $directorats[$FetchDirectorat->Initial] = $FetchDirectorat->Directorat;
        }

        $getDepartment = DB::table("master_department")->orderBy("Department","ASC")->get();
        foreach($getDepartment as $FetchDepartment){
            $departments[$FetchDepartment->Initial] = $FetchDepartment->Department;
        }

        $Query = DB::table("master_hr_approval")->orderBy("Department","ASC")->get();

        foreach($Query as $Fetch){
                echo "<row id=\"$Fetch->ID\">";
                echo "<cell>$nomor</cell>";
                echo "<cell>".htmlspecialchars($directorats[$Fetch->Directorat] ?? $Fetch->Directorat)."</cell>";
                echo "<cell>".htmlspecialchars($departments[$Fetch->Department] ?? $Fetch->Department)."</cell>";
                echo "<cell>".htmlspecialchars($Fetch->Name)."</cell>";
                echo "<cell>".htmlspecialchars($Fetch->Level)."</cell>";
                echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
                echo "</row>";

        $nomor++;
        }

        echo "</rows>";
    }

    public function new()
    {
        $directorats = DB::table('master_directorat')->orderBy('Directorat', 'ASC')->get();
        return view("humanresources.master.approval-new", compact('directorats'));
    }

    public function getDepartmentsFromDirectorat(Request $request)
    {
        $directorat = $request->input('directorat');
        $departments = DB::table('master_department')
            ->where('Directorat', $directorat)
            ->orderBy('Department', 'ASC')
            ->get();

        return response()->json($departments);
    }

    public function getEmployeeFromDepartment(Request $request)
    {   
        $directorat = $request->input('directorat');
        $department = $request->input('department');
        $employee = DB::table('employment')
            ->where("Location", Auth::user()->Location)
            ->where('Directorat', $directorat)
            ->where('Department', $department)
            ->orderBy('Name', 'ASC')
            ->get();

        return response()->json($employee);
    }

    public function newSubmit(Request $request)
    {
        $validatedData = $request->validate([
            'directorat' => 'required|string',
            'department' => 'required|string',
            'name' => 'required|string',
            'level' => 'required|integer',
        ]);

        $getus = DB::table('users')->where("Emp_ID", $validatedData['name'])->first();
        if (!$getus) {
            return response()->json(['message' => 'User not found in the system.', 'status' => 'error'], 404);
        }

        DB::table('master_hr_approval')->insert([
            'Directorat' => $validatedData['directorat'],
            'Department' => $validatedData['department'],
            'Name' => $getus->Name,
            'username' => $getus->username,
            'Level' => $validatedData['level'],
            'Status' => 'ACTIVE', // Assuming new entries are active by default
        ]);

        return response()->json(['message' => 'Approval entry created successfully.', 'status' => 'success']);
    }

    public function detail(Request $request)
    {
        //
    }
}
