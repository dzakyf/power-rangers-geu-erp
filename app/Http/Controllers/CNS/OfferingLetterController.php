<?php

namespace App\Http\Controllers\CNS;

use App\CnsOfferingLetter;
use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Mpdf\Mpdf;
use Spatie\Permission\Models\Permission;

class OfferingLetterController extends Controller
{
    public function index()
    {
        return view('cns.offering-letter.index', [
            'sellers' => $this->getSellers(),
        ]);
    }

    public function xmlData(Request $request)
    {
        if (!headers_sent()) {
            header('Content-Type: text/xml');
        }

        try {
            $query = CnsOfferingLetter::query()->orderByDesc('id');

            if ($request->filled('status')) {
                $query->where('status', $request->query('status'));
            }
            if ($request->filled('seller_code')) {
                $query->where('seller_code', $request->query('seller_code'));
            }
            if ($request->filled('start_date')) {
                $query->whereDate('offer_date', '>=', $request->query('start_date'));
            }
            if ($request->filled('end_date')) {
                $query->whereDate('offer_date', '<=', $request->query('end_date'));
            }

            $rows = $query->get();

            $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<rows>\n";
            $no = 1;
            foreach ($rows as $row) {
                $delivery = $row->delivery_term === 'CIF' && $row->destination_port
                    ? 'CIF ' . $row->destination_port
                    : $row->delivery_term;

                $qtyText = $row->quantity_mt !== null && $row->quantity_mt !== ''
                    ? number_format((float) $row->quantity_mt, 2) . ' MT'
                    : '-';

                $priceText = $row->price_per_mt !== null && $row->price_per_mt !== ''
                    ? ($row->price_currency ?: 'USD') . ' ' . number_format((float) $row->price_per_mt, 2)
                    : '-';

                $statusBadge = $row->status === CnsOfferingLetter::STATUS_APPROVED
                    ? '<span class="badge badge-success px-2 py-1">APPROVED</span>'
                    : '<span class="badge badge-warning px-2 py-1">DRAFT</span>';

                $xml .= '<row id="' . (int) $row->id . '">';
                $xml .= '<cell>' . $no++ . '</cell>';
                $xml .= '<cell>' . htmlspecialchars($row->offer_no) . '</cell>';
                $xml .= '<cell>' . htmlspecialchars($row->offer_date ? $row->offer_date->format('d M Y') : '-') . '</cell>';
                $xml .= '<cell>' . htmlspecialchars($row->seller_name) . '</cell>';
                $xml .= '<cell>' . htmlspecialchars($row->buyer_name ?: '-') . '</cell>';
                $xml .= '<cell>' . htmlspecialchars($qtyText) . '</cell>';
                $xml .= '<cell>' . htmlspecialchars($priceText) . '</cell>';
                $xml .= '<cell>' . htmlspecialchars($delivery ?: '-') . '</cell>';
                $xml .= '<cell>' . htmlspecialchars(CnsOfferingLetter::formatLoadingPeriod($row->loading_period) ?: '-') . '</cell>';
                $xml .= '<cell>' . htmlspecialchars($row->valid_until ? $row->valid_until->format('d M Y') : '-') . '</cell>';
                $xml .= '<cell><![CDATA[' . $statusBadge . ']]></cell>';
                $xml .= "</row>\n";
            }
            $xml .= '</rows>';

            return response($xml, 200)->header('Content-Type', 'text/xml');
        } catch (\Throwable $e) {
            Log::error('CNS Offering Letter xmlData error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<rows></rows>", 200)->header('Content-Type', 'text/xml');
        }
    }

    public function create()
    {
        $today = Carbon::today();
        $sellers = $this->getSellers();
        $firstSeller = $sellers->first();
        $defaultSellerCode = $firstSeller ? $firstSeller->code : 'GEU';
        $defaultPlace = $firstSeller ? $firstSeller->place_of_issue : '-';

        return view('cns.offering-letter.create', [
            'sellers'        => $sellers,
            'signatoryUsers' => $this->getSignatoryUsers(),
            'hsCodes'        => CnsOfferingLetter::hsCodes(),
            'defaults'       => CnsOfferingLetter::defaults(),
            'buyers'         => $this->getBuyers(),
            'offerDate'      => $today->toDateString(),
            'previewNo'      => CnsOfferingLetter::generateOfferNo($defaultSellerCode, $today),
            'defaultPlace'   => $defaultPlace,
        ]);
    }

    public function nextNumber(Request $request)
    {
        $sellerId = $request->query('seller_id');
        $sellerCompany = null;
        if (!empty($sellerId)) {
            $sellerCompany = DB::table('supplier')->where('ID', $sellerId)->first();
        }

        if ($sellerCompany) {
            $sellerCode = CnsOfferingLetter::resolveSellerCode($sellerCompany->Company_Name, $sellerCompany->Supplier_ID);
            $placeOfIssue = !empty($sellerCompany->City) ? $sellerCompany->City : '-';
        } else {
            $rawCode = (string) $request->query('seller_code', 'GEU');
            $sellerCode = CnsOfferingLetter::resolveSellerCode('', $rawCode);
            $placeOfIssue = '-';
        }

        try {
            $date = $request->filled('offer_date')
                ? Carbon::parse($request->query('offer_date'))
                : Carbon::today();
        } catch (\Throwable $e) {
            $date = Carbon::today();
        }

        try {
            return response()->json([
                'offer_no'       => CnsOfferingLetter::generateOfferNo($sellerCode, $date),
                'place_of_issue' => $placeOfIssue,
            ]);
        } catch (\Throwable $e) {
            Log::error('CNS Offering Letter nextNumber error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal generate nomor offer: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $data = $this->validateInput($request);
        $offerDate = Carbon::parse($data['offer_date']);

        $seller = DB::table('supplier')
            ->where('ID', $data['seller_id'])
            ->where('Is_Company', 1)
            ->where('Is_Supplier', 0)
            ->where('Is_Consignee', 0)
            ->first();

        if (!$seller) {
            return back()->withInput()->with('error', 'Seller tidak ditemukan di Master Company.');
        }

        $sellerCode = CnsOfferingLetter::resolveSellerCode($seller->Company_Name, $seller->Supplier_ID);
        $placeOfIssue = !empty($seller->City) ? $seller->City : '-';

        $sigUser = null;
        if (!empty($data['signatory_key'])) {
            $sigUser = $this->getSignatoryUsers()->firstWhere('id', (int) $data['signatory_key']);
            if (!$sigUser) {
                return back()->withInput()->with('error', 'Penandatangan harus user aktif dengan permission Approve Offering Letter.');
            }
        }

        $buyer = DB::table('supplier')
            ->where('ID', $data['buyer_id'])
            ->where('Is_Consignee', 1)
            ->first();

        if (!$buyer) {
            return back()->withInput()->with('error', 'Buyer tidak ditemukan di Master Consignee.');
        }

        DB::beginTransaction();
        try {
            $letter = CnsOfferingLetter::create([
                'offer_no'            => CnsOfferingLetter::generateOfferNo($sellerCode, $offerDate),
                'offer_date'          => $offerDate->toDateString(),
                'seller_id'           => (int) $seller->ID,
                'seller_code'         => $sellerCode,
                'seller_name'         => $seller->Company_Name,
                'place_of_issue'      => $placeOfIssue,
                'buyer_id'            => (int) $buyer->ID,
                'buyer_name'          => $buyer->Company_Name,
                'product'             => $data['product'] ?? null,
                'hs_code'             => $data['hs_code'] ?? null,
                'certificate'         => $data['certificate'] ?? null,
                'spec_ffa'            => $data['spec_ffa'] ?? null,
                'spec_mni'            => $data['spec_mni'] ?? null,
                'spec_unsaponifiable' => $data['spec_unsaponifiable'] ?? null,
                'spec_iodine_value'   => $data['spec_iodine_value'] ?? null,
                'spec_sulphur'        => $data['spec_sulphur'] ?? null,
                'spec_total_metals'   => $data['spec_total_metals'] ?? null,
                'spec_phosphorus'     => $data['spec_phosphorus'] ?? null,
                'spec_total_chloride' => $data['spec_total_chloride'] ?? null,
                'quantity_mt'         => $data['quantity_mt'] ?? null,
                'quantity_note'       => $data['quantity_note'] ?? null,
                'final_qty_quality'   => $data['final_qty_quality'] ?? null,
                'price_currency'      => $data['price_currency'] ?? 'USD',
                'price_per_mt'        => $data['price_per_mt'] ?? null,
                'loading_period'      => $data['loading_period'],
                'delivery_term'       => $data['delivery_term'] ?? null,
                'destination_port'    => ($data['delivery_term'] ?? '') === 'CIF' ? ($data['destination_port'] ?? null) : null,
                'packaging'           => $data['packaging'] ?? null,
                'payment_terms'       => $data['payment_terms'] ?? null,
                'load_port_inspector' => $data['load_port_inspector'] ?? null,
                'signatory_key'       => $sigUser ? (string) $sigUser->id : null,
                'signatory_name'      => $sigUser ? $sigUser->Name : null,
                'signatory_title'     => $data['signatory_title'] ?? ($sigUser ? $sigUser->Position : null),
                'status'              => CnsOfferingLetter::STATUS_DRAFT,
                'created_by'          => Auth::user()->username ?? Auth::user()->name ?? 'system',
            ]);

            DB::commit();

            return redirect()
                ->route('cns-offering-letter.detail', ['id' => $letter->id])
                ->with('success', 'Offering Letter ' . $letter->offer_no . ' berhasil dibuat (Draft).');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('CNS Offering Letter store failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Gagal membuat Offering Letter: ' . $e->getMessage());
        }
    }

    public function detail($id)
    {
        $letter = CnsOfferingLetter::find($id);
        if (!$letter) {
            return redirect()
                ->route('cns-offering-letter.index')
                ->with('error', 'Offering Letter tidak ditemukan.');
        }

        return view('cns.offering-letter.detail', [
            'letter'         => $letter,
            'sellers'        => $this->getSellers(),
            'signatoryUsers' => $this->getSignatoryUsers(),
            'hsCodes'        => CnsOfferingLetter::hsCodes(),
            'buyers'         => $this->getBuyers(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $letter = CnsOfferingLetter::find($id);
        if (!$letter) {
            return redirect()
                ->route('cns-offering-letter.index')
                ->with('error', 'Offering Letter tidak ditemukan.');
        }

        if ($letter->status === CnsOfferingLetter::STATUS_APPROVED) {
            return back()->with('error', 'Offering Letter yang sudah di-approve tidak dapat diubah.');
        }

        $data = $this->validateInput($request);
        $offerDate = Carbon::parse($data['offer_date']);

        $seller = DB::table('supplier')
            ->where('ID', $data['seller_id'])
            ->where('Is_Company', 1)
            ->where('Is_Supplier', 0)
            ->where('Is_Consignee', 0)
            ->first();

        if (!$seller) {
            return back()->withInput()->with('error', 'Seller tidak ditemukan di Master Company.');
        }

        $sellerCode = CnsOfferingLetter::resolveSellerCode($seller->Company_Name, $seller->Supplier_ID);
        $placeOfIssue = !empty($seller->City) ? $seller->City : '-';

        $sigUser = null;
        if (!empty($data['signatory_key'])) {
            $sigUser = $this->getSignatoryUsers()->firstWhere('id', (int) $data['signatory_key']);
            if (!$sigUser) {
                return back()->withInput()->with('error', 'Penandatangan harus user aktif dengan permission Approve Offering Letter.');
            }
        }

        $buyer = DB::table('supplier')
            ->where('ID', $data['buyer_id'])
            ->where('Is_Consignee', 1)
            ->first();

        if (!$buyer) {
            return back()->withInput()->with('error', 'Buyer tidak ditemukan di Master Consignee.');
        }

        $offerNo = $letter->offer_no;
        if (
            $letter->seller_code !== $sellerCode
            || $letter->offer_date->format('Y-m') !== $offerDate->format('Y-m')
        ) {
            $offerNo = CnsOfferingLetter::generateOfferNo($sellerCode, $offerDate);
        }

        DB::beginTransaction();
        try {
            $letter->update([
                'offer_no'            => $offerNo,
                'offer_date'          => $offerDate->toDateString(),
                'seller_id'           => (int) $seller->ID,
                'seller_code'         => $sellerCode,
                'seller_name'         => $seller->Company_Name,
                'place_of_issue'      => $placeOfIssue,
                'buyer_id'            => (int) $buyer->ID,
                'buyer_name'          => $buyer->Company_Name,
                'product'             => $data['product'] ?? null,
                'hs_code'             => $data['hs_code'] ?? null,
                'certificate'         => $data['certificate'] ?? null,
                'spec_ffa'            => $data['spec_ffa'] ?? null,
                'spec_mni'            => $data['spec_mni'] ?? null,
                'spec_unsaponifiable' => $data['spec_unsaponifiable'] ?? null,
                'spec_iodine_value'   => $data['spec_iodine_value'] ?? null,
                'spec_sulphur'        => $data['spec_sulphur'] ?? null,
                'spec_total_metals'   => $data['spec_total_metals'] ?? null,
                'spec_phosphorus'     => $data['spec_phosphorus'] ?? null,
                'spec_total_chloride' => $data['spec_total_chloride'] ?? null,
                'quantity_mt'         => $data['quantity_mt'] ?? null,
                'quantity_note'       => $data['quantity_note'] ?? null,
                'final_qty_quality'   => $data['final_qty_quality'] ?? null,
                'price_currency'      => $data['price_currency'] ?? 'USD',
                'price_per_mt'        => $data['price_per_mt'] ?? null,
                'loading_period'      => $data['loading_period'],
                'delivery_term'       => $data['delivery_term'] ?? null,
                'destination_port'    => ($data['delivery_term'] ?? '') === 'CIF' ? ($data['destination_port'] ?? null) : null,
                'packaging'           => $data['packaging'] ?? null,
                'payment_terms'       => $data['payment_terms'] ?? null,
                'load_port_inspector' => $data['load_port_inspector'] ?? null,
                'signatory_key'       => $sigUser ? (string) $sigUser->id : null,
                'signatory_name'      => $sigUser ? $sigUser->Name : null,
                'signatory_title'     => $data['signatory_title'] ?? ($sigUser ? $sigUser->Position : null),
                'updated_by'          => Auth::user()->username ?? Auth::user()->name ?? 'system',
            ]);

            DB::commit();

            return redirect()
                ->route('cns-offering-letter.detail', ['id' => $letter->id])
                ->with('success', 'Offering Letter ' . $letter->offer_no . ' berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('CNS Offering Letter update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Gagal memperbarui Offering Letter: ' . $e->getMessage());
        }
    }

    public function approve($id)
    {
        $user = Auth::user();
        $approvePerms = $this->existingApprovePermissions();
        $canApprove = $user && (
            (!empty($approvePerms) && $user->hasAnyPermission($approvePerms))
            || $user->username === 'admin'
            || $user->Priv_ID === 'ADM'
        );

        if (!$canApprove) {
            return back()->with('error', 'Anda tidak memiliki permission untuk approve Offering Letter.');
        }

        $letter = CnsOfferingLetter::find($id);
        if (!$letter) {
            return redirect()
                ->route('cns-offering-letter.index')
                ->with('error', 'Offering Letter tidak ditemukan.');
        }

        if ($letter->status === CnsOfferingLetter::STATUS_APPROVED) {
            return back()->with('error', 'Offering Letter sudah di-approve sebelumnya.');
        }

        if (empty($letter->signatory_key)) {
            return back()->with('error', 'Penandatangan belum dipilih. Harap pilih penandatangan terlebih dahulu.');
        }

        $signatoryUser = User::find($letter->signatory_key);
        if (!$signatoryUser) {
            return back()->with('error', 'User penandatangan tidak ditemukan.');
        }

        $sigPath = function_exists('user_signature_path') ? user_signature_path($signatoryUser->id) : null;
        if (!$sigPath || !file_exists($sigPath)) {
            return back()->with('error', "Penandatangan ({$signatoryUser->Name}) belum memiliki file tanda tangan digital di sistem.");
        }

        $ext = strtolower(pathinfo($sigPath, PATHINFO_EXTENSION));
        $mime = $ext === 'jpg' ? 'jpeg' : $ext;
        $signatureUri = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($sigPath));

        $approvedAt = Carbon::now();
        $validUntil = $approvedAt->copy()->addDays(CnsOfferingLetter::VALIDITY_DAYS);

        DB::beginTransaction();
        try {
            $letter->update([
                'status'              => CnsOfferingLetter::STATUS_APPROVED,
                'signatory_signature' => $signatureUri,
                'approved_by'         => Auth::user()->username ?? Auth::user()->name ?? 'system',
                'approved_at'         => $approvedAt,
                'valid_until'         => $validUntil->toDateString(),
                'updated_by'          => Auth::user()->username ?? Auth::user()->name ?? 'system',
            ]);

            DB::commit();

            return redirect()
                ->route('cns-offering-letter.detail', ['id' => $letter->id])
                ->with('success', 'Offering Letter disetujui. Berlaku sampai ' . $validUntil->format('d F Y') . '.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('CNS Offering Letter approve failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal menyetujui Offering Letter: ' . $e->getMessage());
        }
    }

    public function print(Request $request, $id = null)
    {
        try {
            $offerNo = $request->query('Task');
            if ($offerNo) {
                $letter = CnsOfferingLetter::where('offer_no', $offerNo)->first();
            } elseif ($id) {
                $letter = CnsOfferingLetter::find($id);
            } else {
                $letter = null;
            }

            if (!$letter) {
                return redirect()
                    ->route('cns-offering-letter.index')
                    ->with('error', 'Offering Letter tidak ditemukan.');
            }

            $signatureUri = $letter->signatory_signature;
            if (!$signatureUri && $letter->status === CnsOfferingLetter::STATUS_APPROVED) {
                $signatoryUser = User::find($letter->signatory_key);
                if ($signatoryUser) {
                    $sigPath = function_exists('user_signature_path') ? user_signature_path($signatoryUser->id) : null;
                    if ($sigPath && file_exists($sigPath)) {
                        $signatureUri = $this->imageToDataUri($sigPath);
                    }
                }
            }

            $html = view('cns.offering-letter.print', compact('letter', 'signatureUri'))->render();

            $sellerCompany = null;
            if (!empty($letter->seller_id)) {
                $sellerCompany = DB::table('supplier')->where('ID', $letter->seller_id)->first();
            }
            if (!$sellerCompany) {
                if (stripos($letter->seller_name, 'Green Energi Utama') !== false || $letter->seller_code === 'GEU') {
                    $sellerCompany = DB::table('supplier')
                        ->where(function ($q) {
                            $q->where('Company_Name', 'like', '%Green Energi Utama%')
                              ->orWhere('Supplier_ID', 'GEU');
                        })
                        ->first();
                } elseif (
                    stripos($letter->seller_name, 'Greennew') !== false
                    || stripos($letter->seller_name, 'Grennew') !== false
                    || $letter->seller_code === 'GNC'
                ) {
                    $sellerCompany = DB::table('supplier')
                        ->where(function ($q) {
                            $q->where('Company_Name', 'like', '%Greennew%')
                              ->orWhere('Company_Name', 'like', '%Grennew%')
                              ->orWhere('Supplier_ID', 'GNC');
                        })
                        ->first();
                } else {
                    $sellerCompany = DB::table('supplier')
                        ->where(function ($q) use ($letter) {
                            $q->where('Company_Name', 'like', '%' . $letter->seller_name . '%')
                              ->orWhere('Supplier_ID', $letter->seller_code);
                        })
                        ->first();
                }
            }
            if (!$sellerCompany) {
                $sellerCompany = DB::table('supplier')->where('Is_Company', 1)->first();
            }

            $letterheadUri = null;
            $footerUri = null;
            if ($sellerCompany) {
                $lhPath = $sellerCompany->Letterhead ?? $sellerCompany->LetterHead ?? null;
                $letterheadUri = $this->imageToDataUri($lhPath);

                $ftPath = $sellerCompany->Footer ?? null;
                $footerUri = $this->imageToDataUri($ftPath);
            }

            $headerHtml = $letterheadUri
                ? '<div style="margin:0;padding:0;line-height:0;"><img src="' . $letterheadUri . '" style="width:100%;display:block;"></div>'
                : '';
            $footerHtml = $footerUri
                ? '<div style="margin:0;padding:0;line-height:0;"><img src="' . $footerUri . '" style="width:100%;display:block;"></div>'
                : '';

            $tempDir = storage_path('app/mpdf');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0775, true);
            }

            $mpdf = new Mpdf([
                'format'              => [210, 297],
                'tempDir'             => $tempDir,
                'margin_top'          => $headerHtml !== '' ? 0 : 10,
                'margin_bottom'       => $footerHtml !== '' ? 0 : 10,
                'margin_left'         => 10,
                'margin_right'        => 10,
                'margin_header'       => 0,
                'margin_footer'       => 0,
                'setAutoTopMargin'    => $headerHtml !== '' ? 'stretch' : false,
                'setAutoBottomMargin' => $footerHtml !== '' ? 'stretch' : false,
            ]);

            if ($headerHtml !== '') {
                $mpdf->SetHTMLHeader($headerHtml);
            }

            if ($footerHtml !== '') {
                $mpdf->SetHTMLFooter($footerHtml);
            }

            $filename = 'OL_' . str_replace(['/', '\\', ' '], '_', $letter->offer_no) . '.pdf';
            $mpdf->SetTitle($filename);
            $mpdf->WriteHTML($html);

            return response($mpdf->Output($filename, 'S'), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            Log::error('CNS Offering Letter print failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()
                ->route('cns-offering-letter.index')
                ->with('error', 'Gagal mencetak Offering Letter: ' . $e->getMessage());
        }
    }

    private function validateInput(Request $request): array
    {
        $qtyInput = $request->input('quantity_mt');
        $priceInput = $request->input('price_per_mt');

        $request->merge([
            'quantity_mt'  => $qtyInput !== null && $qtyInput !== '' ? str_replace(',', '', (string) $qtyInput) : null,
            'price_per_mt' => $priceInput !== null && $priceInput !== '' ? str_replace(',', '', (string) $priceInput) : null,
        ]);

        return $request->validate([
            'offer_date'          => ['required', 'date'],
            'seller_id'           => ['required', 'integer'],
            'buyer_id'            => ['required', 'integer'],
            'product'             => ['required', 'string', 'max:255'],
            'hs_code'             => ['nullable', 'string', 'max:50'],
            'certificate'         => ['nullable', 'string', 'max:100'],
            'spec_ffa'            => ['nullable', 'string', 'max:100'],
            'spec_mni'            => ['nullable', 'string', 'max:100'],
            'spec_unsaponifiable' => ['nullable', 'string', 'max:100'],
            'spec_iodine_value'   => ['nullable', 'string', 'max:100'],
            'spec_sulphur'        => ['nullable', 'string', 'max:100'],
            'spec_total_metals'   => ['nullable', 'string', 'max:100'],
            'spec_phosphorus'     => ['nullable', 'string', 'max:100'],
            'spec_total_chloride' => ['nullable', 'string', 'max:100'],
            'quantity_mt'         => ['required', 'numeric', 'min:0.01'],
            'quantity_note'       => ['nullable', 'string', 'max:255'],
            'final_qty_quality'   => ['nullable', 'string'],
            'price_currency'      => ['nullable', 'string', 'max:10'],
            'price_per_mt'        => ['required', 'numeric', 'min:0.01'],
            'loading_period'      => ['required', 'string', 'max:100'],
            'delivery_term'       => ['required', Rule::in(['FOB Surabaya', 'CIF'])],
            'destination_port'    => ['nullable', 'required_if:delivery_term,CIF', 'string', 'max:150'],
            'packaging'           => ['nullable', 'string', 'max:150'],
            'payment_terms'       => ['nullable', 'string'],
            'load_port_inspector' => ['nullable', 'string', 'max:255'],
            'signatory_key'       => ['required', 'integer'],
            'signatory_title'     => ['nullable', 'string', 'max:100'],
        ]);
    }

    private function existingApprovePermissions(): array
    {
        return Permission::where('name', 'cns.offering_letter.approve')
            ->where('guard_name', 'web')
            ->pluck('name')
            ->all();
    }

    private function getSignatoryUsers()
    {
        $perms = $this->existingApprovePermissions();
        if (empty($perms)) {
            return collect();
        }

        return User::permission($perms)
            ->where('Status', 'ACTIVE')
            ->orderBy('Name')
            ->get(['id', 'Name', 'Position']);
    }

    private function getBuyers()
    {
        $query = DB::table('supplier')
            ->where('Is_Consignee', 1)
            ->orderBy('Company_Name');

        if (Auth::check() && !empty(Auth::user()->Location)) {
            $query->where('Location', Auth::user()->Location);
        }

        return $query->get(['ID', 'Company_Name']);
    }

    private function getSellers()
    {
        $query = DB::table('supplier')
            ->where('Is_Company', 1)
            ->where('Is_Supplier', 0)
            ->where('Is_Consignee', 0)
            ->orderBy('Company_Name');

        if (Auth::check() && !empty(Auth::user()->Location)) {
            $query->where('Location', Auth::user()->Location);
        }

        $rows = $query->get(['ID', 'Supplier_ID', 'Company_Name', 'City']);

        return $rows->map(function ($row) {
            $resolvedCode = CnsOfferingLetter::resolveSellerCode($row->Company_Name, $row->Supplier_ID);
            return (object) [
                'id'             => (int) $row->ID,
                'supplier_id'    => $row->Supplier_ID,
                'code'           => $resolvedCode,
                'name'           => $row->Company_Name,
                'place_of_issue' => !empty($row->City) ? $row->City : '-',
            ];
        });
    }

    private function imageToDataUri(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '' || preg_match('#^https?://#i', $path)) {
            return null;
        }

        $fullPath = is_file($path) ? $path : public_path(ltrim($path, '/'));
        if (!is_file($fullPath) || !file_exists($fullPath)) {
            $altPath = storage_path('app/public/' . ltrim($path, '/'));
            if (is_file($altPath) && file_exists($altPath)) {
                $fullPath = $altPath;
            } else {
                return null;
            }
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if ($extension === 'png') {
            if (class_exists('\\Imagick')) {
                try {
                    $image = new \Imagick($fullPath);
                    $image->setImageFormat('png');
                    $image->setInterlaceScheme(\Imagick::INTERLACE_NO);
                    $binary = $image->getImageBlob();
                    $image->clear();
                    $image->destroy();
                    if ($binary !== null && $binary !== '') {
                        return 'data:image/png;base64,' . base64_encode($binary);
                    }
                } catch (\Throwable $e) {
                }
            }

            $image = @imagecreatefrompng($fullPath);
            if ($image !== false) {
                imageinterlace($image, false);
                ob_start();
                imagepng($image);
                $binary = ob_get_clean();
                imagedestroy($image);
                if ($binary !== false) {
                    return 'data:image/png;base64,' . base64_encode($binary);
                }
            }
        }

        $mime = $extension === 'jpg' ? 'jpeg' : $extension;
        $binary = @file_get_contents($fullPath);
        if ($binary === false) {
            return null;
        }

        return 'data:image/' . $mime . ';base64,' . base64_encode($binary);
    }
}
