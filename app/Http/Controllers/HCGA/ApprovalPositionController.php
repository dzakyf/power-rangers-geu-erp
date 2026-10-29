<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\Approval\OrganizationChartSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ApprovalPositionController extends Controller
{
    public function index()
    {
        $this->authorizeManagement();

        return view('humanresources.approval.position');
    }

    public function data()
    {
        $this->authorizeManagement();

        if (!headers_sent()) {
            header('Content-type: text/xml; charset=UTF-8');
        }

        $org = DB::table('organization_structure')
            ->select('Position_ID', DB::raw('MIN(ID) AS Org_ID'))
            ->whereNotNull('Position_ID')
            ->groupBy('Position_ID');

        $rows = DB::table('position_assignment as pa')
            ->join('master_position as mp', 'mp.ID', '=', 'pa.Position_ID')
            ->join('users as u', 'u.id', '=', 'pa.User_Id')
            ->leftJoinSub($org, 'ox', function ($join) {
                $join->on('ox.Position_ID', '=', 'mp.ID');
            })
            ->leftJoin('organization_structure as os', 'os.ID', '=', 'ox.Org_ID')
            ->leftJoin('organization_structure as osp', 'osp.ID', '=', 'os.Reports_To_Position_ID')
            ->select(
                'pa.*',
                'mp.Initial',
                'mp.Position',
                'mp.Level',
                'u.Name as User_Name',
                'u.username',
                'os.ID as Org_ID',
                'os.Position_Code as Org_Code',
                'os.Level as Org_Level',
                'osp.Position as Reports_To_Position'
            )
            ->orderBy('mp.Level', 'ASC')
            ->orderBy('mp.Position', 'ASC')
            ->orderBy('u.Name', 'ASC')
            ->get();

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rows>';

        $no = 1;
        foreach ($rows as $row) {
            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Initial . ' - ' . $row->Position) . '</cell>';
            echo '<cell>' . (int) $row->Level . '</cell>';
            echo '<cell>' . $this->xml($row->Org_Code ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Reports_To_Position ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->User_Name) . '</cell>';
            echo '<cell>' . $this->xml($row->username) . '</cell>';
            echo '<cell>' . $this->xml($row->Assignment_Type) . '</cell>';
            echo '<cell>' . $this->xml($row->Location) . '</cell>';
            echo '<cell>' . $this->xml($row->Company_ID) . '</cell>';
            echo '<cell>' . $this->xml($row->Start_Date) . '</cell>';
            echo '<cell>' . $this->xml($row->End_Date) . '</cell>';
            echo '<cell>' . $this->xml($row->Status) . '</cell>';
            echo '</row>';
            $no++;
        }

        echo '</rows>';
    }

    public function detail(Request $request)
    {
        $this->authorizeManagement();

        $id = (int) $request->input('ID');
        $assignment = $id ? DB::table('position_assignment')->where('ID', $id)->first() : null;
        $user = $assignment ? DB::table('users')->where('id', $assignment->User_Id)->first() : null;

        $positions = $this->positionsWithOrg()
            ->where(function ($query) {
                $query->whereNull('mp.Status')->orWhere('mp.Status', 'ACTIVE');
            })
            ->orderBy('mp.Level', 'ASC')
            ->orderBy('mp.Position', 'ASC')
            ->get();

        $selectedOrg = null;
        if ($assignment) {
            $selectedOrg = $positions->firstWhere('ID', (int) $assignment->Position_ID);
        }

        return view('humanresources.approval.position-detail', compact('assignment', 'user', 'positions', 'selectedOrg'));
    }

    private function positionsWithOrg()
    {
        $org = DB::table('organization_structure')
            ->select('Position_ID', DB::raw('MIN(ID) AS Org_ID'))
            ->whereNotNull('Position_ID')
            ->groupBy('Position_ID');

        return DB::table('master_position as mp')
            ->leftJoinSub($org, 'ox', function ($join) {
                $join->on('ox.Position_ID', '=', 'mp.ID');
            })
            ->leftJoin('organization_structure as os', 'os.ID', '=', 'ox.Org_ID')
            ->leftJoin('organization_structure as osp', 'osp.ID', '=', 'os.Reports_To_Position_ID')
            ->select(
                'mp.ID',
                'mp.Initial',
                'mp.Position',
                'mp.Level',
                'mp.Department',
                'mp.Location',
                'mp.Company_ID',
                'os.ID as Org_ID',
                'os.Position_Code as Org_Code',
                'os.Level as Org_Level',
                'os.Reports_To_Position_ID',
                'osp.Position as Reports_To_Position',
                'os.Dept_Name as Org_Dept_Name'
            );
    }

    public function save(Request $request)
    {
        $this->authorizeManagement();

        $id = (int) $request->input('ID');
        $positionId = (int) $request->input('Position_ID');
        $username = trim((string) $request->input('username'));
        $type = strtoupper(trim((string) $request->input('Assignment_Type', 'PRIMARY')));
        $status = strtoupper(trim((string) $request->input('Status', 'ACTIVE')));
        $startDate = $request->input('Start_Date') ?: null;
        $endDate = $request->input('End_Date') ?: null;

        if ($positionId < 1 || $username === '') {
            return redirect('hrd-approval-position')->with('error', 'Position dan username wajib diisi.');
        }
        if (!in_array($type, ['PRIMARY', 'ACTING', 'SECONDARY'], true)) {
            return redirect('hrd-approval-position')->with('error', 'Assignment type tidak valid.');
        }
        if (!in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
            return redirect('hrd-approval-position')->with('error', 'Status assignment tidak valid.');
        }
        if ($startDate && $endDate && $endDate < $startDate) {
            return redirect('hrd-approval-position')->with('error', 'End Date tidak boleh sebelum Start Date.');
        }

        $position = DB::table('master_position')->where('ID', $positionId)->first();
        if (!$position) {
            return redirect('hrd-approval-position')->with('error', 'Position tidak ditemukan.');
        }
        if (empty($position->Location) || empty($position->Company_ID)) {
            return redirect('hrd-approval-position')->with('error', 'Position belum memiliki Location/Company. Jalankan backfill terlebih dahulu.');
        }

        $user = DB::table('users')->where('username', $username)->first();
        if (!$user) {
            return redirect('hrd-approval-position')->with('error', 'User dengan username tersebut tidak ditemukan.');
        }
        if (strtoupper((string) $user->Status) !== 'ACTIVE') {
            return redirect('hrd-approval-position')->with('error', 'User tidak aktif.');
        }

        $duplicate = DB::table('position_assignment')
            ->where('Position_ID', $positionId)
            ->where('User_Id', (int) $user->id)
            ->where('Status', 'ACTIVE')
            ->where('ID', '!=', $id)
            ->exists();

        if ($duplicate && $status === 'ACTIVE') {
            return redirect('hrd-approval-position')->with('error', 'User sudah memiliki assignment ACTIVE pada position tersebut.');
        }

        $data = [
            'Position_ID' => $positionId,
            'User_Id' => (int) $user->id,
            'Emp_ID' => $user->Emp_ID,
            'Location' => $position->Location,
            'Company_ID' => $position->Company_ID,
            'Department' => $user->Department ?: $position->Department,
            'Assignment_Type' => $type,
            'Start_Date' => $startDate,
            'End_Date' => $endDate,
            'Status' => $status,
            'Updated_By' => Auth::user()->username,
            'Updated_At' => now(),
        ];

        if ($id > 0) {
            $assignment = DB::table('position_assignment')->where('ID', $id)->first();
            if (!$assignment) {
                return redirect('hrd-approval-position')->with('error', 'Assignment tidak ditemukan.');
            }

            DB::table('position_assignment')->where('ID', $id)->update($data);
            $message = 'Position assignment berhasil diperbarui.';
        } else {
            $data['Created_By'] = Auth::user()->username;
            $data['Created_At'] = now();
            DB::table('position_assignment')->insert($data);
            $message = 'Position assignment berhasil dibuat.';
        }

        return redirect('hrd-approval-position')->with('success', $message);
    }

    public function sync(OrganizationChartSyncService $sync)
    {
        $this->authorizeManagement();

        $stats = $sync->syncStructure();

        return redirect('hrd-approval-position')->with('success', sprintf(
            'Sync bagan selesai: %s node terhubung, %s hierarki posisi diperbarui.',
            $stats['linked'],
            $stats['hierarchy_updated']
        ));
    }

    public function toggle(Request $request)
    {
        $this->authorizeManagement();

        $id = (int) $request->input('ID');
        $assignment = DB::table('position_assignment')->where('ID', $id)->first();
        if (!$assignment) {
            return redirect('hrd-approval-position')->with('error', 'Assignment tidak ditemukan.');
        }

        $newStatus = strtoupper((string) $assignment->Status) === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';

        if ($newStatus === 'ACTIVE') {
            $duplicate = DB::table('position_assignment')
                ->where('Position_ID', (int) $assignment->Position_ID)
                ->where('User_Id', (int) $assignment->User_Id)
                ->where('Status', 'ACTIVE')
                ->where('ID', '!=', $id)
                ->exists();

            if ($duplicate) {
                return redirect('hrd-approval-position')->with('error', 'Tidak dapat mengaktifkan assignment duplikat.');
            }
        }

        DB::table('position_assignment')->where('ID', $id)->update([
            'Status' => $newStatus,
            'Updated_By' => Auth::user()->username,
            'Updated_At' => now(),
        ]);

        return redirect('hrd-approval-position')->with('success', 'Status assignment diubah menjadi ' . $newStatus . '.');
    }

    private function authorizeManagement(): void
    {
        if (!Gate::allows('approval.policy.manage')) {
            abort(403, 'Hanya wildcard admin atau pemegang izin approval.policy.manage yang dapat mengelola position assignment.');
        }
    }

    private function xml($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}