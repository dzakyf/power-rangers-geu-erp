<?php

namespace App\Http\Controllers\Credential;

use App\Http\Controllers\Controller;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PrivilegeMappingController extends Controller
{
    /**
     * Hak akses pengelolaan aturan mapping (ADM atau pemegang izin role edit)
     */
    private function canManageMapping(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        return $user->hasRole('ADM') || 
               $user->Priv_ID === 'ADM' || 
               $user->can('master.credential.role.edit');
    }

    /**
     * Halaman Utama Pengelolaan Mapping Privilege (Posisi & Direktorat)
     */
    public function index()
    {
        abort_unless($this->canManageMapping(), 403, 'Anda tidak memiliki hak akses mengelola Privilege Mapping.');

        $roles = Role::orderBy('name', 'ASC')->pluck('name');
        
        $directorats = DB::table('master_directorat')
            ->orderBy('Directorat', 'ASC')
            ->get();

        $positions = DB::table('master_position')
            ->orderBy('Position', 'ASC')
            ->get();

        $departments = DB::table('master_department')
            ->orderBy('Department', 'ASC')
            ->get();

        return view('credential.privilege-mapping', compact('roles', 'directorats', 'positions', 'departments'));
    }

    /**
     * Endpoint Data JSON / DataTable untuk list mapping
     */
    public function data(Request $request)
    {
        $query = DB::table('privilege_access_mappings');

        $total = $query->count();

        if ($request->filled('mapping_type')) {
            $query->where('mapping_type', $request->input('mapping_type'));
        }

        if ($request->filled('search.value')) {
            $s = $request->input('search.value');
            $query->where(function ($q) use ($s) {
                $q->where('target_key', 'LIKE', "%{$s}%")
                  ->orWhere('priv_id', 'LIKE', "%{$s}%")
                  ->orWhere('default_role', 'LIKE', "%{$s}%")
                  ->orWhere('description', 'LIKE', "%{$s}%");
            });
        }

        $filtered = $query->count();

        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 15);

        $data = $query->orderBy('mapping_type', 'ASC')
            ->orderBy('target_key', 'ASC')
            ->offset($start)
            ->limit($length)
            ->get();

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    /**
     * Simpan atau Update Mapping Rule
     */
    public function store(Request $request)
    {
        if (!$this->canManageMapping()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'id'           => 'nullable|integer',
            'mapping_type' => 'required|in:POSITION,DIRECTORAT,DEPARTMENT',
            'target_key'   => 'required|string|max:150',
            'priv_id'      => 'required|string|max:50',
            'default_role' => 'nullable|string|max:100',
            'description'  => 'nullable|string|max:255',
            'status'       => 'required|in:ACTIVE,INACTIVE',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $id = $request->input('id');
        $user = Auth::user()->username ?? 'SYSTEM';

        $data = [
            'mapping_type' => $request->input('mapping_type'),
            'target_key'   => trim($request->input('target_key')),
            'priv_id'      => strtoupper(trim($request->input('priv_id'))),
            'default_role' => trim($request->input('default_role')),
            'description'  => $request->input('description'),
            'status'       => $request->input('status', 'ACTIVE'),
            'updated_by'   => $user,
            'updated_at'   => now(),
        ];

        if ($id) {
            DB::table('privilege_access_mappings')->where('id', $id)->update($data);
            $msg = 'Aturan hak akses berhasil diperbarui.';
        } else {
            $data['created_by'] = $user;
            $data['created_at'] = now();
            DB::table('privilege_access_mappings')->insert($data);
            $msg = 'Aturan hak akses baru berhasil ditambahkan.';
        }

        return response()->json([
            'status'  => 'success',
            'message' => $msg,
        ]);
    }

    /**
     * Hapus Mapping Rule
     */
    public function destroy(Request $request)
    {
        if (!$this->canManageMapping()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403);
        }

        $id = $request->input('id');
        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID wajib disertakan.'], 422);
        }

        DB::table('privilege_access_mappings')->where('id', $id)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Aturan hak akses berhasil dihapus.',
        ]);
    }
}

