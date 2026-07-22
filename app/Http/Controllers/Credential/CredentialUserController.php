<?php

namespace App\Http\Controllers\Credential;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CredentialUserController extends Controller
{
    public function index()
    {
        return view('credential.user-management');
    }

    public function createUser()
    {
        return view('credential.user-management');
    }

    public function userListData(Request $request)
    {
        $query = DB::table('users');

        $total = $query->count();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('Name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['id', 'username', 'Name', 'email', 'Location', 'Position', 'Status'];
        $query->orderBy($columns[$orderColumn] ?? 'id', $orderDir);

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $filtered = $query->count();

        $data = $query->offset($start)->limit($length)->get()->map(function ($u) {
            return [
                'id' => $u->id,
                'username' => $u->username,
                'name' => $u->Name,
                'email' => $u->email,
                'location' => $u->Location,
                'position' => $u->Position,
                'status' => $u->Status,
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'records_total' => $total,
            'records_filtered' => $filtered,
            'data' => $data,
        ]);
    }

    public function candidatesWithoutAccount(Request $request)
    {
        $query = DB::table('recruitment as r')
            ->leftJoin('users as u', 'u.username', '=', 'r.Task_ID')
            ->whereNull('u.id')
            ->select('r.ID', 'r.Task_ID', 'r.Name', 'r.Position', 'r.Email', 'r.Handphone', 'r.Created_Date');

        $total = $query->count();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('r.Name', 'like', "%{$search}%")
                  ->orWhere('r.Task_ID', 'like', "%{$search}%");
            });
        }

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['ID', 'Task_ID', 'Name', 'Position', 'Email', 'Handphone', 'Created_Date'];
        $query->orderBy($columns[$orderColumn] ?? 'ID', $orderDir);

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $filtered = $query->count();

        $data = $query->offset($start)->limit($length)->get()->map(function ($c) {
            return [
                'id' => $c->ID,
                'task_id' => $c->Task_ID,
                'name' => $c->Name,
                'position' => $c->Position,
                'email' => $c->Email,
                'handphone' => $c->Handphone,
                'created_date' => $c->Created_Date,
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'records_total' => $total,
            'records_filtered' => $filtered,
            'data' => $data,
        ]);
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
