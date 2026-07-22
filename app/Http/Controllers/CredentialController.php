<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CredentialController extends Controller
{
    public function userManagement()
    {
        return view('credential.user-management');
    }

    public function createUser()
    {
        return view('credential.user-management');
    }

    public function userListData()
    {
        $users = DB::table('users')->orderBy('id', 'DESC')->get();
        $output = "<?xml version='1.0' encoding='UTF-8'?><rows>";
        foreach ($users as $u) {
            $output .= "<row id='{$u->id}'>";
            $output .= "<cell>{$u->id}</cell>";
            $output .= "<cell><![CDATA[{$u->username}]]></cell>";
            $output .= "<cell><![CDATA[{$u->Name}]]></cell>";
            $output .= "<cell><![CDATA[{$u->email}]]></cell>";
            $output .= "<cell>{$u->Location}</cell>";
            $output .= "<cell><![CDATA[{$u->Position}]]></cell>";
            $output .= "<cell>{$u->Status}</cell>";
            $output .= "</row>";
        }
        $output .= "</rows>";
        return response($output)->header('Content-Type', 'text/xml');
    }

    public function candidatesWithoutAccount()
    {
        $candidates = DB::select("
            SELECT r.* FROM recruitment r
            LEFT JOIN users u ON u.username = r.Task_ID
            WHERE u.id IS NULL
            ORDER BY r.ID DESC
        ");
        $output = "<?xml version='1.0' encoding='UTF-8'?><rows>";
        foreach ($candidates as $c) {
            $output .= "<row id='{$c->ID}'>";
            $output .= "<cell>{$c->ID}</cell>";
            $output .= "<cell><![CDATA[{$c->Task_ID}]]></cell>";
            $output .= "<cell><![CDATA[{$c->Name}]]></cell>";
            $output .= "<cell><![CDATA[{$c->Position}]]></cell>";
            $output .= "<cell><![CDATA[{$c->Email}]]></cell>";
            $output .= "<cell>{$c->Handphone}</cell>";
            $output .= "<cell>{$c->Created_Date}</cell>";
            $output .= "</row>";
        }
        $output .= "</rows>";
        return response($output)->header('Content-Type', 'text/xml');
    }

    public function createCandidateAccount(Request $request)
    {
        $taskId = $request->input('Task_ID');
        if (!$taskId) {
            return 'error:Task_ID is required';
        }

        $rec = DB::table('recruitment')->where('Task_ID', $taskId)->first();
        if (!$rec) {
            return 'error:Candidate not found';
        }

        $exists = DB::table('users')->where('username', $taskId)->first();
        if ($exists) {
            return 'error:User account already exists';
        }

        DB::table('users')->insert([
            'username' => $rec->Task_ID,
            'Emp_ID'   => $rec->Task_ID,
            'password' => bcrypt('12345678'),
            'Name'     => $rec->Name,
            'email'    => $rec->Email,
            'Location' => $rec->Location ?? 'GEU-HO',
            'Position' => 'CANDIDATE',
            'Status'   => 'ACTIVE',
            'Sub_Menu' => 'CI00001,CI00002,CI00003,CI00004,CI00005',
        ]);

        return "success:Account created for {$rec->Name}";
    }

    public function createUserSubmit(Request $request)
    {
        $email = $request->input('email');
        $name = $request->input('Name');
        $password = $request->input('password', '12345678');
        $location = $request->input('Location', 'GEU-HO');
        $position = $request->input('Position', 'EMPLOYEE');

        if (!$email || !$name) {
            return 'error:Email/Username and Name are required';
        }

        $exists = DB::table('users')->where('username', $email)->first();
        if ($exists) {
            return 'error:Email/Username already exists';
        }

        DB::table('users')->insert([
            'username' => $email,
            'Name'     => $name,
            'email'    => $email,
            'password' => bcrypt($password),
            'Location' => $location,
            'Position' => $position,
            'Status'   => 'ACTIVE',
        ]);

        return 'success';
    }
}
