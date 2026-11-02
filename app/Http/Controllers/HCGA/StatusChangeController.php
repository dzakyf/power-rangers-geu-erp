<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Form Perubahan Status Karyawan (Personal Status Change Form).
 *
 * Mengikuti pola modul HRD lain (lihat CarRentController / VehicleMaintenanceController):
 *   - index    : halaman grid dhtmlx
 *   - data     : feed XML untuk grid
 *   - new      : formulir pembuatan   (dipanggil LoadM ke #STATUSCHANGE_DTL)
 *   - new-submit
 *   - detail   : tampilan satu record
 *   - print    : PDF via mPDF (bisa ?download=1 untuk mengunduh)
 *
 * Data disimpan pada tabel status_changes (nomor otomatis PS-YYYYMM-#####).
 */
class StatusChangeController extends Controller
{
    /** Pilihan status perkawinan & jumlah tanggungan */
    const MARITAL_OPTIONS = [
        'TK/0' => 'Tidak kawin (TK/0)',
        'TK/1' => 'Tidak kawin (TK/1)',
        'TK/2' => 'Tidak kawin (TK/2)',
        'TK/3' => 'Tidak kawin (TK/3)',
        'TK/4' => 'Tidak kawin (TK/4)',
        'K/0'  => 'Kawin (K/0)',
        'K/1'  => 'Kawin (K/1)',
        'K/2'  => 'Kawin (K/2)',
        'K/3'  => 'Kawin (K/3)',
    ];

    /** Jenis pengajuan yang didukung modul ini (mutasi & promosi memakai tabel yang sama) */
    const JENIS = ['MUTATION', 'PROMOTION'];
    /** Daftar kolom yang boleh dituliskan dari request ke tabel */
    const FIELDS = [
        'employee_name', 'nik', 'join_date',
        'current_position', 'current_division', 'current_department',
        'current_grade', 'current_work_location', 'current_employment_status',
        'current_marital_dependents', 'current_salary', 'current_benefit',
        'current_description',
        'proposed_position', 'proposed_division', 'proposed_department',
        'proposed_grade', 'proposed_work_location', 'proposed_employment_status',
        'proposed_marital_dependents', 'proposed_salary', 'proposed_benefit',
        'proposed_description',
        'effective_date', 'proposed_by',
        'origin_department_head', 'destination_department_head',
        'hcga_verifier', 'director_approver',
    ];

    public function index(Request $request)
    {
        $jenis = strtoupper(trim((string) $request->input('jenis', '')));
        return view('humanresources.employment.status-changes.index', compact('jenis'));
    }

    /**
     * Feed XML grid dhtmlx (/hrd-status-change-data).
     *
     * Urutan <cell> HARUS sama dengan urutan setHeader / attachHeader / setInitWidths /
     * setColAlign / setColTypes / setColSorting pada index.blade.php (kontrak posisional):
     * No., Nomor Form, Jenis, Nama, NIK, Tanggal Join, Jabatan Sekarang,
     * Status Sekarang, Jabatan Usulan, Status Usulan, Gaji Usulan (Rp),
     * Tanggal Efektif, Tanggal Dibuat.
     *
     * Filter: ?q= (nomor form / nama / NIK) dan ?jenis= (MUTATION / PROMOTION).
     */
    public function data(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $getSc = DB::table('status_changes');

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $getSc->where(function ($sub) use ($q) {
                $sub->where('form_number', 'LIKE', '%' . $q . '%')
                    ->orWhere('employee_name', 'LIKE', '%' . $q . '%')
                    ->orWhere('nik', 'LIKE', '%' . $q . '%');
            });
        }

        $jenis = strtoupper(trim((string) $request->input('jenis', '')));
        if (in_array($jenis, self::JENIS, true)) {
            $getSc->where('jenis', $jenis);
        }

        $getSc = $getSc->orderBy('id', 'DESC')->get();

        $nomor = 1;
        echo "<rows>";

        foreach ($getSc as $value) {
            echo "<row id='{$value->id}'>";
            echo "<cell>{$nomor}</cell>";
            echo "<cell>" . htmlspecialchars((string) ($value->form_number ?: '')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($value->jenis ?: 'MUTATION')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($value->employee_name ?: '')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($value->nik ?: '')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($value->join_date ?: '')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($value->current_position ?: '')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($value->current_employment_status ?: '')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($value->proposed_position ?: '')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($value->proposed_employment_status ?: '')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) number_format((float) $value->proposed_salary, 0, ',', '.')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($value->effective_date ?: '')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) (substr((string) $value->created_at, 0, 10))) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function create(Request $request)
    {
        $jenis = strtoupper(trim((string) $request->input('jenis', '')));
        $master = $this->masterOptions();

        return view('humanresources.employment.status-changes.new', array_merge($master, [
            'sc'              => null,
            'maritalOptions'  => self::MARITAL_OPTIONS,
            'jenisDefault'    => in_array($jenis, self::JENIS, true) ? $jenis : '',
            'proposerDefault' => $this->currentProposer(),
        ]));
    }

    public function store(Request $request)
    {
        list($err, $clean) = $this->clean($request);

        if ($err) {
            return response()->json(['status' => 'error', 'message' => $err], 422);
        }

        DB::beginTransaction();
        try {
            $clean['jenis'] = $this->resolveJenis($request, $clean);

            $now = now();
            $clean['created_at'] = $now;
            $clean['updated_at'] = $now;

            $newId = DB::table('status_changes')->insertGetId($clean);

            // Nomor form dibuat otomatis setelah data tersimpan: PS-YYYYMM-#####
            $formNumber = 'PS-' . date('Ym') . '-' . str_pad((string) $newId, 5, '0', STR_PAD_LEFT);
            DB::table('status_changes')->where('id', $newId)->update([
                'form_number' => $formNumber,
            ]);

            // -- Hubungkan ke riwayat karyawan (employment_history) --
            $emp = !empty($clean['nik'])
                ? DB::table('employment')->where('NIK', $clean['nik'])->orderBy('ID', 'ASC')->first()
                : null;

            $catatan = 'Jenis: ' . $clean['jenis'];

            if ((string) $clean['current_position'] !== '' || (string) $clean['proposed_position'] !== '') {
                $catatan .= '; Jabatan: ' . ($clean['current_position'] ?: '-') . ' -> ' . ($clean['proposed_position'] ?: '-');
            }
            if ((string) $clean['current_department'] !== '' || (string) $clean['proposed_department'] !== '') {
                $catatan .= '; Departemen: ' . ($clean['current_department'] ?: '-') . ' -> ' . ($clean['proposed_department'] ?: '-');
            }
            if ((string) $clean['current_work_location'] !== '' || (string) $clean['proposed_work_location'] !== '') {
                $catatan .= '; Lokasi: ' . ($clean['current_work_location'] ?: '-') . ' -> ' . ($clean['proposed_work_location'] ?: '-');
            }
            if ($clean['current_salary'] != $clean['proposed_salary']) {
                $catatan .= '; Salary: ' . number_format((float) $clean['current_salary'], 0, ',', '.') . ' -> ' . number_format((float) $clean['proposed_salary'], 0, ',', '.');
            }

            DB::table('employment_history')->insert([
                'Task_ID'      => $emp->Task_ID ?? '',
                'NIK'          => $clean['nik'],
                'Date_Process' => $now,
                'Activity'     => 'PERUBAHAN STATUS',
                'Reference'    => $formNumber,
                'Notes'        => $catatan,
                'Start_Date'   => $clean['effective_date'] ?: date('Y-m-d'),
                'End_Date'     => '',
                'Remark'       => $clean['jenis'] === 'PROMOTION' ? 'Promosi' : 'Mutasi',
                'User_Trx'     => Auth::user()->username ?? '',
                'Date_Trx'     => $now,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('StatusChange create failed: ' . $e->getMessage());

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'status'      => 'success',
            'message'     => 'Form perubahan status berhasil dibuat.',
            'form_number' => $formNumber,
            'ID'          => $newId,
            'jenis'       => $clean['jenis'],
        ]);
    }

    /**
     * API pencarian karyawan (tabel employment) untuk autoisi form:
     * section Identitas Karyawan & Data Saat Ini diisi langsung dari data master.
     * ?q= mencocokkan Nama / NIK, Status ACTIVE, maksimal 10 baris.
     */
    public function employeeSearch(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $query = DB::table('employment')
            ->where('Status', 'ACTIVE');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('Name', 'LIKE', '%' . $q . '%')
                    ->orWhere('NIK', 'LIKE', '%' . $q . '%');
            });
        }

        // Kolom diambil eksplisit — kolom KTP tidak diambil.
        $rows = $query->select([
            'ID', 'Task_ID', 'NIK', 'Name', 'Date_in', 'Marital_Status',
            'Position', 'Directorat', 'Department', 'Subsection',
            'Employment_Grade', 'Location', 'Employment_Class',
            'Employment_Status', 'Salary_Accepted',
        ])->orderBy('Name', 'ASC')->limit(10)->get();

        $maritalValid = array_keys(self::MARITAL_OPTIONS);

        $data = [];
        foreach ($rows as $r) {
            // Status perkawinan dipakai dari employment.Marital_Status bila termasuk
            // pilihan form (TK/0 - K/3); di luar itu (SINGLE, MARRIED, K/4, kosong)
            // dibiarkan -- Pilih -- agar tidak membuat nilai tidak valid.
            $marital = trim((string) $r->Marital_Status);
            if (!in_array($marital, $maritalValid, true)) {
                $marital = '';
            }

            // Coba resolusi dari master_position (Initial / Initial_Code / nama posisi).
            // Jika tidak ketemu, gunakan nilai mentah dari employment.
            $posCode = trim((string) $r->Position);
            $mp = null;
            if ($posCode !== '') {
                $mp = DB::table('master_position')->where('Status', 'ACTIVE')
                    ->where(function ($sub) use ($posCode) {
                        $sub->where('Initial', $posCode)
                            ->orWhere('Initial_Code', $posCode)
                            ->orWhere('Position', $posCode);
                    })
                    ->orderBy('Level')->orderBy('ID')->first();
            }

            if ($mp) {
                $jabatan = $mp->Position;
                $divisi = $mp->Directorat ?: ($r->Directorat ?? '');
                $departemen = $mp->Department ?: ($r->Department ?? '');
                $resolusi = 'MASTER';
            } else {
                $jabatan = $r->Position ?? '';
                $divisi = $r->Directorat ?? '';
                $departemen = $r->Department ?? '';
                $resolusi = 'EMPLOYMENT';
            }

            $data[] = [
                'Task_ID'            => $r->Task_ID ?? '',
                'Name'               => $r->Name,
                'NIK'                => $r->NIK,
                'join_date'          => $r->Date_in ? date('Y-m-d', strtotime($r->Date_in)) : '',
                'Marital_Status'     => $marital,
                'Position'           => $jabatan,
                'Division'           => $divisi,
                'Department'         => $departemen,
                'Subsection'         => $r->Subsection ?? '',
                'Employment_Grade'   => $r->Employment_Grade ?? '',
                'Location'           => $r->Location ?? '',
                'Employment_Class'   => $r->Employment_Class ?? '',
                'Employment_Status'  => $r->Employment_Status ?? '',
                'Salary'             => $r->Salary_Accepted !== null ? (float) $r->Salary_Accepted : '',
                'Jabatan_Sumber'     => $resolusi,
            ];
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * API pencarian pengusul ("Diusulkan oleh") — dari users + employment.
     * Yang disimpan ke status_changes.proposed_by adalah "Nama (username)"
     * sehingga tetap bisa ditelusuri ke akun user-nya.
     */
    public function proposerSearch(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $query = DB::table('users')
            ->leftJoin('employment', 'employment.NIK', '=', 'users.Emp_ID')
            ->where('users.Status', 'ACTIVE')
            ->select([
                'users.username', 'users.Name as User_Name', 'users.Emp_ID',
                'users.Position as User_Position', 'users.Department as User_Department',
                'employment.Name as Emp_Name', 'employment.Position as Emp_Position',
                'employment.Department as Emp_Department',
            ]);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('users.username', 'LIKE', '%' . $q . '%')
                    ->orWhere('users.Name', 'LIKE', '%' . $q . '%')
                    ->orWhere('users.Emp_ID', 'LIKE', '%' . $q . '%')
                    ->orWhere('employment.Name', 'LIKE', '%' . $q . '%');
            });
        }

        $rows = $query->orderBy('users.Name')->limit(10)->get();

        $data = [];
        foreach ($rows as $r) {
            $nama = trim((string) ($r->Emp_Name ?: $r->User_Name));

            $data[] = [
                'username'   => $r->username,
                'nik'        => $r->Emp_ID ?: '',
                'name'       => $nama !== '' ? $nama : $r->username,
                'position'   => $r->Emp_Position ?: ($r->User_Position ?: ''),
                'department' => $r->Emp_Department ?: ($r->User_Department ?: ''),
                'label'      => ($nama !== '' ? $nama : $r->username) . ' (' . $r->username . ')',
            ];
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function detail(Request $request)
    {
        $sc = DB::table('status_changes')->where('id', (int) $request->ID)->first();

        if (!$sc) {
            return redirect('hrd-status-change-index');
        }

        return view('humanresources.employment.status-changes.detail', compact('sc'));
    }

    public function edit(Request $request)
    {
        $sc = DB::table('status_changes')->where('id', (int) $request->ID)->first();

        if (!$sc) {
            return redirect('hrd-status-change-index');
        }

        return view('humanresources.employment.status-changes.edit', array_merge($this->masterOptions(), [
            'sc'             => $sc,
            'maritalOptions' => self::MARITAL_OPTIONS,
            'proposerDefault' => $sc->proposed_by ?: $this->currentProposer(),
        ]));
    }

    /**
     * Label pengusul default: user yang sedang login, "Nama (username)".
     */
    private function currentProposer()
    {
        if (!Auth::check()) {
            return '';
        }

        $user = Auth::user();
        $emp = $user->Emp_ID ? DB::table('employment')->where('NIK', $user->Emp_ID)->first() : null;

        $nama = trim((string) ($emp->Name ?? '')) ?: trim((string) ($user->Name ?? ''));

        return ($nama !== '' ? $nama : $user->username) . ' (' . $user->username . ')';
    }

    /**
     * Opsi master untuk dropdown form: Jabatan / Divisi / Departemen dari master_position.
     */
    private function masterOptions()
    {
        return [
            'positions' => DB::table('master_position')
                ->where('Status', 'ACTIVE')
                ->orderBy('Department')->orderBy('Level')->orderBy('Position')
                ->get(['Position', 'Department', 'Directorat', 'Initial', 'Level']),
            'divisions' => DB::table('master_position')
                ->where('Status', 'ACTIVE')
                ->whereNotNull('Directorat')->where('Directorat', '!=', '')
                ->distinct()->orderBy('Directorat')->pluck('Directorat')->all(),
            'departments' => DB::table('master_position')
                ->where('Status', 'ACTIVE')
                ->whereNotNull('Department')->where('Department', '!=', '')
                ->distinct()->orderBy('Department')->pluck('Department')->all(),
        ];
    }

    public function update(Request $request)
    {
        $id = (int) $request->ID;

        if (!DB::table('status_changes')->where('id', $id)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
        }

        list($err, $clean) = $this->clean($request);

        if ($err) {
            return response()->json(['status' => 'error', 'message' => $err], 422);
        }

        DB::beginTransaction();
        try {
            $clean['jenis'] = $this->resolveJenis($request, $clean);
            $clean['updated_at'] = now();
            DB::table('status_changes')->where('id', $id)->update($clean);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('StatusChange update failed: ' . $e->getMessage());

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Form perubahan status berhasil diperbarui.',
        ]);
    }

    public function delete(Request $request)
    {
        $id = (int) $request->ID;

        if (!DB::table('status_changes')->where('id', $id)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
        }

        try {
            DB::table('status_changes')->where('id', $id)->delete();
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Form perubahan status berhasil dihapus.',
        ]);
    }

    /**
     * Cetak form perubahan status sebagai PDF (mPDF).
     * Tambahkan ?download=1 pada URL untuk mengunduh, default tampil di browser.
     */
    public function printForm(Request $request)
    {
        $sc = DB::table('status_changes')->where('id', (int) $request->ID)->first();

        if (!$sc) {
            abort(404);
        }

        // Logo: dibaca dari isi file aslinya (bukan ekstensi), diratakan ke latar putih,
        // lalu dikirim sebagai data URI agar tidak jadi hitam di mPDF.
        $logo = $this->logoDataUri(public_path('assets/images/small-logo.png'));

        $html = view('humanresources.employment.status-changes.print', [
            'statusChange' => $sc,
            'logo'         => $logo,
        ])->render();

        // Folder temp mPDF di storage agar tidak bermasalah permission di vendor/
        $tempDir = storage_path('app/mpdf');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'orientation'   => 'P',
            'margin_left'   => 8,
            'margin_right'  => 8,
            'margin_top'    => 8,
            'margin_bottom' => 8,
            'tempDir'       => $tempDir,
        ]);

        $mpdf->SetTitle('Form Perubahan Status - ' . ($sc->form_number ?: (string) $sc->id));
        $mpdf->WriteHTML($html);

        $suffix = \Illuminate\Support\Str::slug($sc->form_number ?: ($sc->nik ?: (string) $sc->id));
        $filename = 'Form-Perubahan-Status-' . $suffix . '.pdf';
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response(
            $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            ]
        );
    }

    /**
     * Jenis pengajuan: dipilih manual dari form, atau deteksi otomatis bila kosong.
     *
     * Aturan deteksi (server-side, bisa ditimpa manual dari dropdown):
     *   - PROMOTION : ada kenaikan gaji, atau grade naik (perbandingan angka bila keduanya angka),
     *                 atau jabatan berubah dengan kenaikan gaji.
     *   - MUTATION  : selain di atas (pindah lokasi/divisi/departemen/jabatan/status
     *                 tanpa kenaikan atasan), termasuk tidak ada perubahan sama sekali.
     */
    private function resolveJenis(Request $request, array $clean)
    {
        $manual = strtoupper(trim((string) $request->input('jenis', '')));

        if (in_array($manual, self::JENIS, true)) {
            return $manual;
        }

        return $this->autoJenis($clean);
    }

    private function autoJenis(array $clean)
    {
        // Nilai gaji dianggap berlaku hanya bila diisi (> 0).
        $currentSalary = (float) $clean['current_salary'];
        $proposedSalary = (float) $clean['proposed_salary'];

        $salaryUp = $currentSalary > 0 && $proposedSalary > $currentSalary;

        // Grade: bandingkan angka bila keduanya berisi angka (mis. "3" -> "4"),
        // jika format lain ("GRADE A") perbedaan tidak dianggap kenaikan.
        $gradeUp = false;
        $currentGrade = trim((string) $clean['current_grade']);
        $proposedGrade = trim((string) $clean['proposed_grade']);

        if ($currentGrade !== '' && $proposedGrade !== ''
            && is_numeric($currentGrade) && is_numeric($proposedGrade)) {
            $gradeUp = (float) $proposedGrade > (float) $currentGrade;
        }

        if ($salaryUp || $gradeUp) {
            return 'PROMOTION';
        }

        // Elemen mudasi: lokasi/divisi/departemen/jabatan/status berubah.
        foreach (['work_location', 'division', 'department', 'position', 'employment_status'] as $field) {
            if (strcasecmp(trim((string) $clean['proposed_' . $field]), trim((string) $clean['current_' . $field])) !== 0) {
                return 'MUTATION';
            }
        }

        // Tidak ada perubahan teridentifikasi — masukkan sebagai mutasi agar tetap tercatat.
        return 'MUTATION';
    }

    /**
     * Normalisasi request menjadi kolom tabel status_changes.
     * Mengembalikan [pesan error / null, array kolom].
     */
    private function clean(Request $request)
    {
        $marital = array_keys(self::MARITAL_OPTIONS);

        $dateFields = ['join_date', 'effective_date'];

        foreach ($dateFields as $field) {
            $value = trim((string) $request->input($field, ''));
            if ($value !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return [$field . ' harus berformat YYYY-MM-DD.', null];
            }
        }

        foreach (['current_marital_dependents', 'proposed_marital_dependents'] as $field) {
            $value = $request->input($field);
            if ($value !== null && $value !== '' && !in_array($value, $marital, true)) {
                return ['Nilai ' . $field . ' tidak valid.', null];
            }
        }

        $salaryFields = ['current_salary', 'proposed_salary'];
        foreach ($salaryFields as $field) {
            $value = $request->input($field);
            if ($value !== null && $value !== '' && !is_numeric($value)) {
                return ['Nilai ' . $field . ' harus berupa angka.', null];
            }
        }

        $data = [];

        foreach (self::FIELDS as $field) {
            $value = $request->input($field);

            if (in_array($field, $dateFields, false)) {
                $data[$field] = $value !== '' ? $value : null;
                continue;
            }

            if (in_array($field, $salaryFields, false)) {
                $data[$field] = ($value === null || $value === '') ? 0 : (float) $value;
                continue;
            }

            $data[$field] = $value === null ? null : $value;
        }

        if (trim((string) $data['employee_name']) === '') {
            return ['Nama karyawan wajib diisi.', null];
        }
        if (trim((string) $data['nik']) === '') {
            return ['NIK wajib diisi.', null];
        }

        $data['employee_name'] = trim((string) $data['employee_name']);
        $data['nik'] = trim((string) $data['nik']);

        return [null, $data];
    }

    /**
     * Ubah file logo menjadi PNG opaque (latar putih) dalam bentuk data URI.
     * Memakai imagecreatefromstring() sehingga format asli terdeteksi otomatis
     * (PNG / JPG / WebP walaupun ekstensinya .png) dan transparansi tidak jadi hitam di mPDF.
     */
    private function logoDataUri($path, $scale = 3)
    {
        if (!is_file($path) || !function_exists('imagecreatefromstring')) {
            return null;
        }

        $src = @imagecreatefromstring(file_get_contents($path));
        if (!$src) {
            return null;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $dst = imagecreatetruecolor($w * $scale, $h * $scale);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w * $scale, $h * $scale, $w, $h);

        ob_start();
        imagepng($dst);
        $png = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return 'data:image/png;base64,' . base64_encode($png);
    }
}