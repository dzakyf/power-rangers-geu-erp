<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\PermitLicenseMailService;
use App\Services\PermitLicenseReminderService;
use App\Services\UploadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PermitLicenseController extends Controller
{
    const MENU_ID = 'SI01007';
    const DOCUMENT_TOPIC = 'permit-license';

    protected $uploadService;
    protected $reminderService;
    protected $mailService;

    public function __construct(UploadService $uploadService, PermitLicenseReminderService $reminderService, PermitLicenseMailService $mailService)
    {
        $this->uploadService = $uploadService;
        $this->reminderService = $reminderService;
        $this->mailService = $mailService;
    }

    public function index()
    {
        $this->authorizePermit('view');

        // Safe to call on every page load: the reminder log makes it idempotent.
        try {
            $this->reminderService->syncExpiryNotifications();
        } catch (\Exception $e) {
            // A reminder failure must not block the permit list.
        }

        $categories = $this->categories();
        $expiring = $this->reminderService->expiringPermits(50);
        $expiredCount = $this->reminderService->expiredCount();
        $defaultReminderDays = PermitLicenseReminderService::DEFAULT_REMINDER_DAYS;
        $reminderService = $this->reminderService;
        $mailEnabled = $this->mailService->isEnabled();
        $mailRecipientCount = count($this->mailService->activeRecipients());

        return view('humanresources.permit-license.index', compact(
            'categories',
            'expiring',
            'expiredCount',
            'defaultReminderDays',
            'reminderService',
            'mailEnabled',
            'mailRecipientCount'
        ));
    }

    public function data()
    {
        $this->authorizePermit('view');

        if (!headers_sent()) {
            header('Content-type: text/xml; charset=UTF-8');
        }

        $rows = DB::table('permit_licenses as p')
            ->leftJoin('permit_license_categories as c', 'c.ID', '=', 'p.Category_ID')
            ->leftJoin('meta_files as mf', 'mf.ID', '=', 'p.Meta_File_ID')
            ->select(
                'p.*',
                'c.Code as Category_Code',
                'c.Name as Category_Name',
                'mf.Filename as Document_Filename',
                'mf.Path as Document_Path'
            )
            ->orderByRaw('CASE WHEN p.Valid_To IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('p.Valid_To', 'ASC')
            ->orderBy('p.ID', 'DESC')
            ->get();

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rows>';

        $no = 1;
        foreach ($rows as $row) {
            $status = $this->reminderService->statusFor($row);
            $validity = $this->reminderService->validityLabel($row);
            $pic = trim((string) $row->PIC_Name);
            if (!empty($row->PIC_Contact)) {
                $pic = trim($pic . ($pic !== '' ? ' / ' : '') . $row->PIC_Contact);
            }

            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->Category_Code ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Category_Name ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Name) . '</cell>';
            echo '<cell>' . $this->xml($row->Issuer ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Permit_Number ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($this->reminderService->formatDate($row->Issue_Date)) . '</cell>';
            echo '<cell>' . $this->xml($validity) . '</cell>';
            echo '<cell>' . $this->xml($pic !== '' ? $pic : '-') . '</cell>';
            echo '<cell>' . (!empty($row->Meta_File_ID) ? 'Ada' : '-') . '</cell>';
            echo '<cell>' . $this->xml($status['label']) . '</cell>';
            echo '</row>';
            $no++;
        }

        echo '</rows>';
    }

    public function detail(Request $request)
    {
        $this->authorizePermit('view');

        $id = (int) $request->input('ID', 0);
        $permit = $id > 0 ? DB::table('permit_licenses')->where('ID', $id)->first() : null;

        if ($id > 0 && !$permit) {
            abort(404, 'Data permit/license tidak ditemukan.');
        }

        $categories = $this->categories();
        $defaultReminderDays = PermitLicenseReminderService::DEFAULT_REMINDER_DAYS;

        return view('humanresources.permit-license.detail', compact(
            'permit',
            'categories',
            'defaultReminderDays'
        ));
    }

    public function save(Request $request)
    {
        $id = (int) $request->input('ID', 0);
        $this->authorizePermit($id > 0 ? 'edit' : 'create');

        try {
            $validated = $request->validate([
                'Category_ID' => 'required|integer',
                'Name' => 'required|string|max:255',
                'Issuer' => 'nullable|string|max:255',
                'Permit_Number' => 'nullable|string|max:150',
                'Issue_Date' => 'nullable|date',
                'Validity_Type' => 'required|in:TEXT,END_DATE,PERIOD',
                'Validity_Text' => 'nullable|string|max:2000',
                'Valid_From' => 'nullable|date',
                'Valid_To' => 'nullable|date',
                'PIC_Name' => 'nullable|string|max:150',
                'PIC_Contact' => 'nullable|string|max:150',
                'Reminder_Days' => 'nullable|integer|min:1|max:365',
                'Document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:20480',
            ], [
                'Category_ID.required' => 'Kode perizinan wajib dipilih.',
                'Category_ID.integer' => 'Kode perizinan tidak valid.',
                'Name.required' => 'Nama perizinan wajib diisi.',
                'Validity_Type.required' => 'Tipe masa berlaku wajib dipilih.',
                'Validity_Type.in' => 'Tipe masa berlaku tidak valid.',
                'Document.mimes' => 'Dokumen harus berupa PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, atau XLSX.',
                'Document.max' => 'Ukuran dokumen maksimal 20 MB.',
            ]);
        } catch (ValidationException $e) {
            return redirect('hrd-permit-license-index')
                ->withErrors($e->errors())
                ->withInput()
                ->with('open_permit_detail', $id);
        }

        $categoryId = (int) $validated['Category_ID'];
        if (!DB::table('permit_license_categories')->where('ID', $categoryId)->exists()) {
            return redirect('hrd-permit-license-index')
                ->withErrors(['Category_ID' => 'Kode perizinan tidak ditemukan.'])
                ->withInput()
                ->with('open_permit_detail', $id);
        }

        $validityType = strtoupper($validated['Validity_Type']);
        $validityText = trim((string) $request->input('Validity_Text', ''));
        $validFrom = $this->normalizeDate($request->input('Valid_From'));
        $validTo = $this->normalizeDate($request->input('Valid_To'));

        if ($validityType === 'TEXT' && $validityText === '') {
            return redirect('hrd-permit-license-index')
                ->withErrors(['Validity_Text' => 'Masa berlaku berupa teks wajib diisi.'])
                ->withInput()
                ->with('open_permit_detail', $id);
        }

        if ($validityType === 'END_DATE' && empty($validTo)) {
            return redirect('hrd-permit-license-index')
                ->withErrors(['Valid_To' => 'Tanggal akhir masa berlaku wajib diisi.'])
                ->withInput()
                ->with('open_permit_detail', $id);
        }

        if ($validityType === 'PERIOD') {
            if (empty($validFrom) || empty($validTo)) {
                return redirect('hrd-permit-license-index')
                    ->withErrors(['Valid_From' => 'Tanggal awal dan akhir periode wajib diisi.'])
                    ->withInput()
                    ->with('open_permit_detail', $id);
            }

            if (Carbon::parse($validTo)->lt(Carbon::parse($validFrom))) {
                return redirect('hrd-permit-license-index')
                    ->withErrors(['Valid_To' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.'])
                    ->withInput()
                    ->with('open_permit_detail', $id);
            }
        }

        $oldPermit = null;
        if ($id > 0) {
            $oldPermit = DB::table('permit_licenses')->where('ID', $id)->first();
            if (!$oldPermit) {
                return redirect('hrd-permit-license-index')->with('error', 'Data permit/license tidak ditemukan.');
            }
        }

        $username = Auth::user()->username;
        $now = now();
        $reminderDays = (int) ($request->input('Reminder_Days') ?: PermitLicenseReminderService::DEFAULT_REMINDER_DAYS);

        $data = [
            'Category_ID' => $categoryId,
            'Name' => trim($validated['Name']),
            'Issuer' => $this->nullableString($request->input('Issuer')),
            'Permit_Number' => $this->nullableString($request->input('Permit_Number')),
            'Issue_Date' => $this->normalizeDate($request->input('Issue_Date')),
            'Validity_Type' => $validityType,
            'Validity_Text' => $validityType === 'TEXT' ? $validityText : null,
            'Valid_From' => $validityType === 'PERIOD' ? $validFrom : null,
            'Valid_To' => in_array($validityType, ['END_DATE', 'PERIOD'], true) ? $validTo : null,
            'PIC_Name' => $this->nullableString($request->input('PIC_Name')),
            'PIC_Contact' => $this->nullableString($request->input('PIC_Contact')),
            'Reminder_Days' => $reminderDays,
            'Updated_By' => $username,
            'updated_at' => $now,
        ];

        $oldMetaId = $oldPermit ? $oldPermit->Meta_File_ID : null;
        $newMetaId = null;
        $removeDocument = (bool) $request->input('Remove_Document', 0);
        $hasFile = $request->hasFile('Document');
        $formId = $id;

        DB::beginTransaction();
        try {
            if ($id > 0) {
                DB::table('permit_licenses')->where('ID', $id)->update($data);
            } else {
                $insert = $data;
                $insert['Created_By'] = $username;
                $insert['created_at'] = $now;
                $id = DB::table('permit_licenses')->insertGetId($insert);
            }

            if ($hasFile) {
                $newMetaId = $this->uploadService->storeFile(
                    $request->file('Document'),
                    'file',
                    self::DOCUMENT_TOPIC,
                    $id,
                    'Dokumen ' . $data['Name']
                );

                DB::table('permit_licenses')->where('ID', $id)->update([
                    'Meta_File_ID' => $newMetaId,
                    'updated_at' => $now,
                ]);
            } elseif ($removeDocument && $oldMetaId) {
                DB::table('permit_licenses')->where('ID', $id)->update([
                    'Meta_File_ID' => null,
                    'updated_at' => $now,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            $cleanupMeta = null;
            if ($newMetaId) {
                $cleanupMeta = DB::table('meta_files')->where('ID', $newMetaId)->first();
            }

            DB::rollBack();

            if ($cleanupMeta) {
                $this->deletePhysicalFile($cleanupMeta);
            }

            return redirect('hrd-permit-license-index')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())
                ->withInput()
                ->with('open_permit_detail', $formId);
        }

        if ($oldMetaId && ($hasFile || $removeDocument)) {
            $this->deleteMetaFile($oldMetaId);
        }

        try {
            $this->reminderService->syncExpiryNotifications();
        } catch (\Exception $e) {
            // Saving the permit succeeded; do not turn a reminder issue into an error.
        }

        return redirect('hrd-permit-license-index')->with('success', 'Data permit/license berhasil disimpan.');
    }

    public function delete(Request $request)
    {
        $this->authorizePermit('delete');

        $id = (int) $request->input('ID', 0);
        $permit = $id > 0 ? DB::table('permit_licenses')->where('ID', $id)->first() : null;
        if (!$permit) {
            return redirect('hrd-permit-license-index')->with('error', 'Data permit/license tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            DB::table('permit_license_reminder_log')->where('Permit_ID', $id)->delete();
            DB::table('permit_licenses')->where('ID', $id)->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect('hrd-permit-license-index')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }

        $this->deleteMetaFile($permit->Meta_File_ID);

        return redirect('hrd-permit-license-index')->with('success', 'Data permit/license berhasil dihapus.');
    }

    public function document(Request $request)
    {
        $this->authorizePermit('view');

        $permit = $this->findPermitWithDocument($request);
        $meta = DB::table('meta_files')->where('ID', $permit->Meta_File_ID)->first();
        $path = public_path('filevendor/' . $meta->Path);

        if (!is_file($path)) {
            abort(404, 'File dokumen tidak ditemukan.');
        }

        return response()->file($path);
    }

    public function download(Request $request)
    {
        $this->authorizePermit('view');

        $permit = $this->findPermitWithDocument($request);
        $meta = DB::table('meta_files')->where('ID', $permit->Meta_File_ID)->first();
        $path = public_path('filevendor/' . $meta->Path);

        if (!is_file($path)) {
            abort(404, 'File dokumen tidak ditemukan.');
        }

        $downloadName = $this->safeFileName($permit->Name . (!empty($permit->Permit_Number) ? ' - ' . $permit->Permit_Number : ''));
        $downloadName .= '.' . ($meta->Extension ?: 'pdf');

        return response()->download($path, $downloadName);
    }

    public function syncReminder()
    {
        $this->authorizePermit('edit');

        try {
            $count = $this->reminderService->syncExpiryNotifications(true);
            $mailRetry = $this->mailService->retryFailedEmails();
            $message = 'Sinkronisasi notifikasi selesai. ' . $count . ' izin diproses.';
            if ($mailRetry > 0) {
                $message .= ' ' . $mailRetry . ' email pengingat berhasil dikirim ulang.';
            }
            return redirect('hrd-permit-license-index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect('hrd-permit-license-index')
                ->with('error', 'Gagal menyinkronkan notifikasi: ' . $e->getMessage());
        }
    }

    public function mailSettings()
    {
        $this->authorizePermit('edit');

        $settings = $this->mailService->settings();
        $mailEnabled = $this->mailService->isEnabled();
        $mailRecipientCount = count($this->mailService->activeRecipients());

        return view('humanresources.permit-license.mail-settings', compact(
            'settings',
            'mailEnabled',
            'mailRecipientCount'
        ));
    }

    public function mailSettingsSave(Request $request)
    {
        $this->authorizePermit('edit');

        try {
            $validated = $request->validate([
                'Subject_Template' => 'required|string|max:255',
                'Intro_Text' => 'nullable|string|max:2000',
                'Footer_Text' => 'nullable|string|max:2000',
            ]);
        } catch (ValidationException $e) {
            return redirect('hrd-permit-license-mail-settings')
                ->withErrors($e->validator)
                ->withInput();
        }

        $validated['Updated_By'] = Auth::user()->username;

        $saved = $this->mailService->updateSettings($validated);

        if (!$saved) {
            return redirect('hrd-permit-license-mail-settings')
                ->with('error', 'Tabel template email belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        return redirect('hrd-permit-license-mail-settings')
            ->with('success', 'Template email berhasil disimpan.');
    }

    public function mailPreview()
    {
        $this->authorizePermit('view');

        $permit = DB::table('permit_licenses as p')
            ->leftJoin('permit_license_categories as c', 'c.ID', '=', 'p.Category_ID')
            ->select('p.*', 'c.Code as Category_Code', 'c.Name as Category_Name')
            ->orderBy('p.ID', 'ASC')
            ->first();

        return response($this->mailService->previewHtml($permit))
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    protected function findPermitWithDocument(Request $request)
    {
        $id = (int) $request->input('ID', 0);
        $permit = $id > 0 ? DB::table('permit_licenses')->where('ID', $id)->first() : null;

        if (!$permit || empty($permit->Meta_File_ID)) {
            abort(404, 'Dokumen permit/license tidak ditemukan.');
        }

        if (!DB::table('meta_files')->where('ID', $permit->Meta_File_ID)->exists()) {
            abort(404, 'Dokumen permit/license tidak ditemukan.');
        }

        return $permit;
    }

    protected function categories()
    {
        if (!Schema::hasTable('permit_license_categories')) {
            return collect();
        }

        return DB::table('permit_license_categories')
            ->orderBy('Sort_Order', 'ASC')
            ->orderBy('Code', 'ASC')
            ->get();
    }

    protected function authorizePermit($action = 'view')
    {
        if (!can_menu(self::MENU_ID, $action) && !Auth::user()->hasRole('ADM')) {
            abort(403, 'Anda tidak berhak mengakses menu Permit & License.');
        }
    }

    protected function nullableString($value)
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    protected function normalizeDate($value)
    {
        if (empty($value) || $value === '0000-00-00') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function deleteMetaFile($metaId)
    {
        if (empty($metaId)) {
            return;
        }

        $stillUsed = DB::table('permit_licenses')->where('Meta_File_ID', $metaId)->exists();
        if ($stillUsed) {
            return;
        }

        $meta = DB::table('meta_files')->where('ID', $metaId)->first();
        if (!$meta) {
            return;
        }

        $this->deletePhysicalFile($meta);
        DB::table('meta_files')->where('ID', $metaId)->delete();
    }

    protected function deletePhysicalFile($meta)
    {
        if (!$meta || empty($meta->Path)) {
            return;
        }

        $path = public_path('filevendor/' . $meta->Path);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    protected function safeFileName($value)
    {
        $value = preg_replace('/[^A-Za-z0-9 _\-]/', '', (string) $value);
        $value = trim(preg_replace('/\s+/', ' ', $value));

        return $value !== '' ? $value : 'dokumen-perizinan';
    }

    protected function xml($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
