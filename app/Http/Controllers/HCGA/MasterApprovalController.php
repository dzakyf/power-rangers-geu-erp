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
        $approvalMenu = DB::table('master_hr_approval_menu')->orderBy('Menu_Name', 'ASC')->get();
        return view("humanresources.master.approval-new", compact('directorats','approvalMenu'));
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
        ]);

        $getus = DB::table('users')->where("Emp_ID", $validatedData['name'])->first();
        if (!$getus) {
            return response()->json(['message' => 'User not found in the system.', 'status' => 'error'], 404);
        }

        $getPosition = DB::table('master_position')->where("Initial", $getus->Position)->first();

        DB::table('master_hr_approval')->insert([
            'Location' => Auth::user()->Location,
            'Directorat' => $validatedData['directorat'],
            'Department' => $validatedData['department'],
            'Name' => $getus->Name,
            'username' => $getus->username,
            'Level' => $getPosition->Level,
            'Menu_List' => implode(',', $request->menu),
            'Status' => 'ACTIVE', // Assuming new entries are active by default
        ]);
        if (in_array($getPosition->Level, [1,2,3,4,5,6,7,8])) {
            $getDept = DB::table('master_department')->where("Initial", $getPosition->Department)->first();
            $getSect = DB::table('master_section')->where("Initial", $getPosition->Subsection)->first();
            DB::table('organization_structure')->insert([
                'Name' => $getus->Name,
                'NIK' => $getus->Emp_ID,
                'Level' . $getPosition->Level => 1,
                'Department' => $getDept->Initial ?? '',
                'Dept_Code' => $getDept->Initial_Code ?? '',
                'Email' => $getus->Email ?? '',
                'Alerted' => 0,
                'divisi' => $getSect->Initial ?? '',
            ]);
        }


        return response()->json(['message' => 'Approval entry created successfully.', 'status' => 'success']);
    }

    public function detail(Request $request)
    {
        $detail = DB::table('master_hr_approval')->where("ID", $request->task)->first();
        $detail->Menu_List = explode(',', $detail->Menu_List) ?? [];
        $approvalMenu = DB::table('master_hr_approval_menu')->orderBy('Menu_Name', 'ASC')->get();
        $getUser = DB::table('users')->where("username", $detail->username)->first();
        if (!$detail) {
            return redirect()->back()->with('error', 'Approval entry not found.');
        }
        $directorats = DB::table('master_directorat')->orderBy('Directorat', 'ASC')->get();
        $departments = DB::table('master_department')->where("Directorat", $detail->Directorat)->orderBy('Department', 'ASC')->get();
        $employee = DB::table('employment')->where("Department", $detail->Department)->get();
        return view('humanresources.master.approval-detail', compact('detail', 'directorats', 'departments', 'employee', 'getUser', 'approvalMenu'));
    }

    public function detailSubmit(Request $request)
    {
        $validatedData = $request->validate([
            'directorat' => 'required|string',
            'department' => 'required|string',
            'name' => 'required|string',
        ]);

        $getus = DB::table('users')->where("Emp_ID", $validatedData['name'])->first();
        if (!$getus) {
            return response()->json(['message' => 'User not found in the system.', 'status' => 'error'], 404);
        }

        $getPosition = DB::table('master_position')->where("Initial", $getus->Position)->first();

        DB::table('master_hr_approval')->where('ID', $request->ID)->update([
            'Location' => Auth::user()->Location,
            'Directorat' => $validatedData['directorat'],
            'Department' => $validatedData['department'],
            'Name' => $getus->Name,
            'username' => $getus->username,
            'Level' => $getPosition->Level,
            'Menu_List' => implode(',', $request->menu),
            'Status' => 'ACTIVE', // Assuming new entries are active by default
        ]);

        $org = DB::table('organization_structure')->where("NIK", $getus->Emp_ID)->first();
        
        if (in_array($getPosition->Level, [1,2,3,4,5,6,7,8]) || $org) {
            $getDept = DB::table('master_department')->where("Initial", $getPosition->Department)->first();
            $getSect = DB::table('master_section')->where("Initial", $getPosition->Subsection)->first();

            if (!$org) {
                DB::table('organization_structure')->insert([
                    'Name' => $getus->Name,
                    'NIK' => $getus->Emp_ID,
                    'Level' . $getPosition->Level => 1,
                    'Department' => $getDept->Initial ?? '',
                    'Dept_Code' => $getDept->Initial_Code ?? '',
                    'Email' => $getus->Email ?? '',
                    'Alerted' => 0,
                    'divisi' => $getSect->Initial ?? '',
                ]);
            }else {
                $arr = [
                    'Name' => $getus->Name,
                    'NIK' => $getus->Emp_ID,
                    'Department' => $getDept->Initial ?? '',
                    'Dept_Code' => $getDept->Initial_Code ?? '',
                    'Email' => $getus->Email ?? '',
                    'Alerted' => 0,
                    'divisi' => $getSect->Initial ?? '',
                ];
    
                for ($i=1; $i <= 8; $i++) { 
                    $lev = "Level".$i;
                    $hasil = 0;
                    if ($i == $getPosition->Level) {
                        $hasil = 1;
                    }
                    $arr[$lev] = $hasil;
                }
    
                DB::table('organization_structure')->where('ID', $org->ID)->update($arr);
            }
        }

        return response()->json(['message' => 'Approval entry created successfully.', 'status' => 'success']);
    }
}
