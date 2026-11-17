<?php

namespace App\Http\Controllers\CNS;

use App\CnsCustomerKyc;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

class CustomerKycController extends Controller
{
    public function index()
    {
        return view('cns.customer-compliance.kyc.index');
    }

    public function xmlData(Request $request)
    {
        if (!headers_sent()) {
            header('Content-Type: text/xml');
        }

        try {
            $query = CnsCustomerKyc::query()->orderByDesc('id');

            if ($request->filled('search')) {
                $term = '%' . trim($request->query('search')) . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('company_name', 'like', $term)
                      ->orWhere('kyc_no', 'like', $term)
                      ->orWhere('company_reg_number', 'like', $term)
                      ->orWhere('filler_name', 'like', $term);
                });
            }
            if ($request->filled('start_date')) {
                $query->whereDate('form_date', '>=', $request->query('start_date'));
            }
            if ($request->filled('end_date')) {
                $query->whereDate('form_date', '<=', $request->query('end_date'));
            }

            $rows = $query->get();

            $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<rows>\n";
            $no = 1;
            foreach ($rows as $row) {
                $dateFormatted = $row->form_date ? $row->form_date->format('d/m/Y') : '-';
                $docsCount = 0;
                if ($row->doc_register_extract) $docsCount++;
                if ($row->doc_certificate) $docsCount++;
                $docText = $docsCount > 0 ? "{$docsCount} Dokumen" : 'Belum Ada';

                $xml .= "<row id=\"{$row->id}\">\n";
                $xml .= "  <cell>{$no}</cell>\n";
                $xml .= '  <cell><![CDATA[' . htmlspecialchars($row->kyc_no ?? '', ENT_QUOTES) . "]]></cell>\n";
                $xml .= '  <cell><![CDATA[' . htmlspecialchars($dateFormatted, ENT_QUOTES) . "]]></cell>\n";
                $xml .= '  <cell><![CDATA[' . htmlspecialchars($row->company_name ?? '', ENT_QUOTES) . "]]></cell>\n";
                $xml .= '  <cell><![CDATA[' . htmlspecialchars($row->filler_name ?? '', ENT_QUOTES) . "]]></cell>\n";
                $xml .= '  <cell><![CDATA[' . htmlspecialchars($row->legal_entity_type ?: '-', ENT_QUOTES) . "]]></cell>\n";
                $xml .= '  <cell><![CDATA[' . htmlspecialchars($row->trading_contact_person ?: '-', ENT_QUOTES) . "]]></cell>\n";
                $xml .= '  <cell><![CDATA[' . htmlspecialchars($docText, ENT_QUOTES) . "]]></cell>\n";
                $xml .= '  <cell><![CDATA[' . htmlspecialchars($row->created_by ?: 'system', ENT_QUOTES) . "]]></cell>\n";
                $xml .= "</row>\n";
                $no++;
            }
            $xml .= "</rows>";

            return response($xml, 200, ['Content-Type' => 'text/xml']);
        } catch (\Throwable $e) {
            Log::error('CNS Customer KYC xmlData error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<rows></rows>", 200, ['Content-Type' => 'text/xml']);
        }
    }

    public function create()
    {
        return view('cns.customer-compliance.kyc.create', [
            'suppliers' => $this->getSuppliers(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateInput($request);
        $formDate = Carbon::parse($validated['form_date']);

        $docRegisterPath = null;
        $docRegisterName = null;
        if ($request->hasFile('doc_register_extract')) {
            $file = $request->file('doc_register_extract');
            $ext = $file->getClientOriginalExtension();
            $filename = 'KYC_REG_' . date('YmdHis') . '_' . Str::random(6) . '.' . $ext;
            $docRegisterPath = $file->storeAs('cns/kyc-docs', $filename, 'local');
            $docRegisterName = $file->getClientOriginalName();
        }

        $docCertPath = null;
        $docCertName = null;
        if ($request->hasFile('doc_certificate')) {
            $file = $request->file('doc_certificate');
            $ext = $file->getClientOriginalExtension();
            $filename = 'KYC_CERT_' . date('YmdHis') . '_' . Str::random(6) . '.' . $ext;
            $docCertPath = $file->storeAs('cns/kyc-docs', $filename, 'local');
            $docCertName = $file->getClientOriginalName();
        }

        DB::beginTransaction();
        try {
            $kycNo = CnsCustomerKyc::generateKycNo($formDate);

            $kyc = CnsCustomerKyc::create([
                'kyc_no'                   => $kycNo,
                'filler_name'              => $validated['filler_name'],
                'form_date'                => $formDate->toDateString(),
                'supplier_id'              => $validated['supplier_id'] ?? null,
                'company_name'             => $validated['company_name'],
                'company_reg_number'       => $validated['company_reg_number'] ?? null,
                'incorporation_date'       => !empty($validated['incorporation_date']) ? Carbon::parse($validated['incorporation_date'])->toDateString() : null,
                'registered_address'       => $validated['registered_address'] ?? null,
                'admin_accounting_address' => $validated['admin_accounting_address'] ?? null,
                'legal_entity_type'        => $validated['legal_entity_type'] ?? null,
                'vat_number'               => $validated['vat_number'] ?? null,
                'website'                  => $validated['website'] ?? null,
                'trading_contact_person'   => $validated['trading_contact_person'] ?? null,
                'trading_contact_details'  => $validated['trading_contact_details'] ?? null,
                'finance_contact_person'   => $validated['finance_contact_person'] ?? null,
                'finance_contact_details'  => $validated['finance_contact_details'] ?? null,
                'business_activities'      => $validated['business_activities'] ?? null,
                'countries_of_activities'  => $validated['countries_of_activities'] ?? null,
                'doc_register_extract'     => $docRegisterPath,
                'doc_register_extract_name'=> $docRegisterName,
                'doc_certificate'          => $docCertPath,
                'doc_certificate_name'     => $docCertName,
                'location'                 => Auth::user()->Location ?? 'MLG',
                'created_by'               => Auth::user()->username ?? Auth::user()->name ?? 'system',
            ]);

            DB::commit();

            return redirect()
                ->route('cns-customer-compliance-kyc.detail', ['id' => $kyc->id])
                ->with('success', 'Form KYC ' . $kyc->kyc_no . ' berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('CNS Customer KYC store failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Gagal menyimpan Form KYC: ' . $e->getMessage());
        }
    }

    public function detail($id)
    {
        $kyc = CnsCustomerKyc::find($id);
        if (!$kyc) {
            return redirect()
                ->route('cns-customer-compliance-kyc.index')
                ->with('error', 'Form KYC tidak ditemukan.');
        }

        return view('cns.customer-compliance.kyc.detail', [
            'kyc' => $kyc,
        ]);
    }

    public function edit($id)
    {
        $kyc = CnsCustomerKyc::find($id);
        if (!$kyc) {
            return redirect()
                ->route('cns-customer-compliance-kyc.index')
                ->with('error', 'Form KYC tidak ditemukan.');
        }

        return view('cns.customer-compliance.kyc.edit', [
            'kyc'       => $kyc,
            'suppliers' => $this->getSuppliers(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $kyc = CnsCustomerKyc::find($id);
        if (!$kyc) {
            return redirect()
                ->route('cns-customer-compliance-kyc.index')
                ->with('error', 'Form KYC tidak ditemukan.');
        }

        $validated = $this->validateInput($request);
        $formDate = Carbon::parse($validated['form_date']);

        $docRegisterPath = $kyc->doc_register_extract;
        $docRegisterName = $kyc->doc_register_extract_name;
        if ($request->hasFile('doc_register_extract')) {
            $file = $request->file('doc_register_extract');
            $ext = $file->getClientOriginalExtension();
            $filename = 'KYC_REG_' . date('YmdHis') . '_' . Str::random(6) . '.' . $ext;
            $docRegisterPath = $file->storeAs('cns/kyc-docs', $filename, 'local');
            $docRegisterName = $file->getClientOriginalName();
        }

        $docCertPath = $kyc->doc_certificate;
        $docCertName = $kyc->doc_certificate_name;
        if ($request->hasFile('doc_certificate')) {
            $file = $request->file('doc_certificate');
            $ext = $file->getClientOriginalExtension();
            $filename = 'KYC_CERT_' . date('YmdHis') . '_' . Str::random(6) . '.' . $ext;
            $docCertPath = $file->storeAs('cns/kyc-docs', $filename, 'local');
            $docCertName = $file->getClientOriginalName();
        }

        DB::beginTransaction();
        try {
            $kyc->update([
                'filler_name'              => $validated['filler_name'],
                'form_date'                => $formDate->toDateString(),
                'supplier_id'              => $validated['supplier_id'] ?? null,
                'company_name'             => $validated['company_name'],
                'company_reg_number'       => $validated['company_reg_number'] ?? null,
                'incorporation_date'       => !empty($validated['incorporation_date']) ? Carbon::parse($validated['incorporation_date'])->toDateString() : null,
                'registered_address'       => $validated['registered_address'] ?? null,
                'admin_accounting_address' => $validated['admin_accounting_address'] ?? null,
                'legal_entity_type'        => $validated['legal_entity_type'] ?? null,
                'vat_number'               => $validated['vat_number'] ?? null,
                'website'                  => $validated['website'] ?? null,
                'trading_contact_person'   => $validated['trading_contact_person'] ?? null,
                'trading_contact_details'  => $validated['trading_contact_details'] ?? null,
                'finance_contact_person'   => $validated['finance_contact_person'] ?? null,
                'finance_contact_details'  => $validated['finance_contact_details'] ?? null,
                'business_activities'      => $validated['business_activities'] ?? null,
                'countries_of_activities'  => $validated['countries_of_activities'] ?? null,
                'doc_register_extract'     => $docRegisterPath,
                'doc_register_extract_name'=> $docRegisterName,
                'doc_certificate'          => $docCertPath,
                'doc_certificate_name'     => $docCertName,
                'updated_by'               => Auth::user()->username ?? Auth::user()->name ?? 'system',
            ]);

            DB::commit();

            return redirect()
                ->route('cns-customer-compliance-kyc.detail', ['id' => $kyc->id])
                ->with('success', 'Form KYC ' . $kyc->kyc_no . ' berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('CNS Customer KYC update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Gagal memperbarui Form KYC: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $kyc = CnsCustomerKyc::find($id);
            if (!$kyc) {
                return redirect()
                    ->route('cns-customer-compliance-kyc.index')
                    ->with('error', 'Form KYC tidak ditemukan.');
            }

            $kyc->delete();

            return redirect()
                ->route('cns-customer-compliance-kyc.index')
                ->with('success', 'Form KYC berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('CNS Customer KYC destroy failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal menghapus Form KYC: ' . $e->getMessage());
        }
    }

    public function downloadDoc($id, $type)
    {
        $kyc = CnsCustomerKyc::find($id);
        if (!$kyc) {
            return redirect()
                ->route('cns-customer-compliance-kyc.index')
                ->with('error', 'Form KYC tidak ditemukan.');
        }

        if ($type === 'register') {
            $path = $kyc->doc_register_extract;
            $name = $kyc->doc_register_extract_name ?: 'commercial_register_extract';
        } elseif ($type === 'certificate') {
            $path = $kyc->doc_certificate;
            $name = $kyc->doc_certificate_name ?: 'sustainability_certificate';
        } else {
            abort(404);
        }

        if (!$path || !Storage::disk('local')->exists($path)) {
            return back()->with('error', 'File dokumen tidak ditemukan.');
        }

        return Storage::disk('local')->download($path, $name);
    }

    public function printPdf($id)
    {
        try {
            $kyc = CnsCustomerKyc::find($id);
            if (!$kyc) {
                return redirect()
                    ->route('cns-customer-compliance-kyc.index')
                    ->with('error', 'Form KYC tidak ditemukan.');
            }

            $headerPath = public_path('assets/images/surat/header_geu.jpg');
            $footerPath = public_path('assets/images/surat/footer_geu.png');

            $headerUri = null;
            if (file_exists($headerPath)) {
                $headerUri = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($headerPath));
            }

            $footerUri = null;
            if (file_exists($footerPath)) {
                $footerUri = 'data:image/png;base64,' . base64_encode(file_get_contents($footerPath));
            }

            $html = view('cns.customer-compliance.kyc.print', compact('kyc', 'headerUri', 'footerUri'))->render();

            $tempDir = storage_path('app/mpdf');
            if (!file_exists($tempDir)) {
                @mkdir($tempDir, 0775, true);
            }

            $mpdf = new Mpdf([
                'tempDir'       => $tempDir,
                'mode'          => 'utf-8',
                'format'        => 'A4',
                'margin_left'   => 15,
                'margin_right'  => 15,
                'margin_top'    => 12,
                'margin_bottom' => 12,
                'default_font'  => 'dejavusans',
            ]);

            $mpdf->WriteHTML($html);

            $cleanNo = str_replace(['/', '\\'], '_', (string) $kyc->kyc_no);
            $filename = 'KYC_' . $cleanNo . '.pdf';

            return response($mpdf->Output($filename, 'I'), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            Log::error('CNS Customer KYC printPdf failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()
                ->route('cns-customer-compliance-kyc.index')
                ->with('error', 'Gagal mencetak PDF KYC: ' . $e->getMessage());
        }
    }

    protected function validateInput(Request $request): array
    {
        return $request->validate([
            'filler_name'              => ['required', 'string', 'max:150'],
            'form_date'                => ['required', 'date'],
            'supplier_id'              => ['nullable', 'integer'],
            'company_name'             => ['required', 'string', 'max:255'],
            'company_reg_number'       => ['nullable', 'string', 'max:100'],
            'incorporation_date'       => ['nullable', 'date'],
            'registered_address'       => ['nullable', 'string'],
            'admin_accounting_address' => ['nullable', 'string'],
            'legal_entity_type'        => ['nullable', 'string', 'max:100'],
            'vat_number'               => ['nullable', 'string', 'max:100'],
            'website'                  => ['nullable', 'string', 'max:255'],
            'trading_contact_person'   => ['nullable', 'string', 'max:150'],
            'trading_contact_details'  => ['nullable', 'string'],
            'finance_contact_person'   => ['nullable', 'string', 'max:150'],
            'finance_contact_details'  => ['nullable', 'string'],
            'business_activities'      => ['nullable', 'string'],
            'countries_of_activities'  => ['nullable', 'string'],
            'doc_register_extract'     => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'doc_certificate'          => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ]);
    }

    protected function getSuppliers()
    {
        $query = DB::table('supplier')
            ->orderBy('Company_Name');

        if (Auth::check() && !empty(Auth::user()->Location)) {
            $query->where('Location', Auth::user()->Location);
        }

        return $query->get(['ID', 'Company_Name', 'Address', 'Phone', 'HP', 'Email']);
    }
}
