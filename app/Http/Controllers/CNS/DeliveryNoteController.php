<?php

namespace App\Http\Controllers\CNS;

use App\DeliveryNote;
use App\Http\Controllers\Controller;
use App\Services\CNS\CnsCodeGenerator;
use App\Support\DhtmlxGridPagination;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mpdf\Mpdf;

class DeliveryNoteController extends Controller
{
    public function indexDeliveryNote()
    {
        return view('cns.export.dn');
    }

    public function indexDeliveryNoteXml(Request $request)
    {
        $companies = [];
        $supplierQuery = DB::table('supplier');
        if (auth()->check() && !empty(auth()->user()->Location)) {
            $company = $supplierQuery->where("Location", auth()->user()->Location)->get();
        } else {
            $company = $supplierQuery->get();
        }
        foreach ($company as $value) {
            $companies[$value->Supplier_ID] = $value->Company_Name;
        }

        $query = DeliveryNote::query();

        $colMap = [
            1 => 'DN_No',
            3 => 'Delivery_Date',
            5 => 'Contract_No',
            6 => 'Product',
            7 => 'Total_Tonnage',
            8 => 'Country_Of_Origin',
            9 => 'Status',
        ];

        $paginated = DhtmlxGridPagination::paginateQuery($query, $request, [
            'col_map' => $colMap,
            'default_page_size' => 25,
        ]);

        $bodyXml = '';
        $nomor = $paginated['posStart'] + 1;

        foreach ($paginated['rows'] as $value) {
            $issuerName = $companies[$value->Shipper] ?? $value->Shipper;
            $consigneeName = $companies[$value->Consignee] ?? $value->Consignee;
            $bodyXml .= "<row id=\"$value->ID\">";
            $bodyXml .= "<cell>$nomor</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->DN_No)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($issuerName ?: '-')."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Delivery_Date)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($consigneeName)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Contract_No)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Product)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Total_Tonnage)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Country_Of_Origin)."</cell>";
            $bodyXml .= "<cell>".htmlspecialchars($value->Status)."</cell>";
            $bodyXml .= "</row>\n";

            $nomor++;
        }

        return DhtmlxGridPagination::response($paginated['total'], $paginated['posStart'], $bodyXml);
    }

    public function printDeliveryNote(Request $request)
    {
        $task = trim((string) $request->input('Task', ''));
        $dn = DeliveryNote::where('DN_No', $task)->first();
        if (!$dn && is_numeric($task)) {
            $dn = DeliveryNote::find($task);
        }

        if (!$dn) {
            abort(404, 'Delivery Note tidak ditemukan');
        }

        $companies = [];
        $supplierQuery = DB::table('supplier');
        if (auth()->check() && !empty(auth()->user()->Location)) {
            $suppliers = $supplierQuery->where("Location", auth()->user()->Location)->get();
        } else {
            $suppliers = $supplierQuery->get();
        }
        foreach ($suppliers as $value) {
            $companies[$value->Supplier_ID] = $value;
        }

        if (!empty($dn->Shipper) && !isset($companies[$dn->Shipper])) {
            $shipperRow = DB::table('supplier')->where('Supplier_ID', $dn->Shipper)->first();
            if ($shipperRow) {
                $companies[$dn->Shipper] = $shipperRow;
            }
        }
        if (!empty($dn->Consignee) && !isset($companies[$dn->Consignee])) {
            $consigneeRow = DB::table('supplier')->where('Supplier_ID', $dn->Consignee)->first();
            if ($consigneeRow) {
                $companies[$dn->Consignee] = $consigneeRow;
            }
        }

        $issuer = $companies[$dn->Shipper] ?? null;
        if (!$issuer) {
            $issuer = DB::table('supplier')
                ->where('Is_Consignee', 0)
                ->where('Is_Company', 1)
                ->where('Is_Supplier', 0)
                ->first()
                ?? DB::table('supplier')->where('Is_Company', 1)->first()
                ?? DB::table('supplier')->first();
        }

        $letterheadUri = $issuer ? $this->imageToDataUri($issuer->Letterhead ?? $issuer->LetterHead ?? null) : null;
        $footerUri = $issuer ? $this->imageToDataUri($issuer->Footer ?? null) : null;

        $headerHtml = $letterheadUri
            ? '<div style="margin:0;padding:0;line-height:0;"><img src="' . $letterheadUri . '" style="width:100%;display:block;"></div>'
            : '';
        $footerHtml = $footerUri
            ? '<div style="margin:0;padding:0;line-height:0;"><img src="' . $footerUri . '" style="width:100%;display:block;"></div>'
            : '';

        $approverId = $dn->signatory_key ?: $dn->approved_by;
        $approver = $approverId ? User::find($approverId) : null;
        $approverSignature = null;
        if ($approver && $dn->Status === 'APPROVED' && function_exists('user_signature_path')) {
            $sigPath = user_signature_path($approver->id);
            if ($sigPath && file_exists($sigPath)) {
                $approverSignature = $this->imageToDataUri($sigPath);
            }
        }

        $html = view('cns.export.print.dn', compact('dn', 'companies', 'issuer', 'approver', 'approverSignature'))->render();

        $tempDir = storage_path('app/mpdf');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'format'              => 'A4',
            'tempDir'             => $tempDir,
            'margin_top'          => $headerHtml !== '' ? 0 : 10,
            'margin_bottom'       => $footerHtml !== '' ? 0 : 10,
            'margin_left'         => 0,
            'margin_right'        => 0,
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

        $filename = 'DN_' . str_replace(['/', '\\', ' '], '_', $dn->DN_No) . '.pdf';
        $mpdf->SetTitle($filename);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function newDeliveryNote()
    {
        $companies = $this->locationSuppliers();
        $issuers = $this->issuerCompanies();
        $contracts = $this->completedContracts();
        $bookingConfirmations = $this->availableBookingConfirmations();
        $approvers = function_exists('cns_eligible_approvers') ? cns_eligible_approvers() : collect();
        $DnNo = CnsCodeGenerator::nextDnNo();

        return view('cns.export.dn-new', compact('companies', 'issuers', 'contracts', 'bookingConfirmations', 'approvers', 'DnNo'));
    }

    public function newDeliveryNoteSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token']);

        $bcNo = trim((string) ($r['BC_No'] ?? ''));
        if ($bcNo === '') {
            return response()->json(['status' => 'error', 'message' => 'Nomor BC (Booking Confirmation) wajib dipilih.'], 422);
        }

        $bcRecord = $this->findBookingConfirmation($bcNo);
        if (!$bcRecord) {
            return response()->json(['status' => 'error', 'message' => 'Booking Confirmation (BC No) tidak ditemukan pada Vessel Schedule.'], 422);
        }

        $r['BC_No'] = $bcNo;
        if (empty($r['Contract_No']) && !empty($bcRecord->Contract_No)) {
            $r['Contract_No'] = $bcRecord->Contract_No;
        }

        $r = $this->withSignatoryFields($r);
        $r['Created_By'] = auth()->user()->username;
        $r['Created_Date'] = now();
        $r['Status'] = 'DRAFT';

        try {
            $duplicate = DB::transaction(function () use ($r, $bcNo) {
                if (DeliveryNote::where('BC_No', $bcNo)->lockForUpdate()->exists()) {
                    return true;
                }
                $r['DN_No'] = CnsCodeGenerator::nextDnNo($r['Delivery_Date'] ?? null, true);
                DeliveryNote::create($r);
                return false;
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateEntryException($e)) {
                return response()->json(['status' => 'error', 'message' => "Delivery Note untuk BC {$bcNo} sudah pernah dibuat."], 422);
            }
            throw $e;
        }

        if ($duplicate) {
            return response()->json(['status' => 'error', 'message' => "Delivery Note untuk BC {$bcNo} sudah pernah dibuat."], 422);
        }

        echo "success";
    }

    public function detailDeliveryNote(Request $request)
    {
        $companies = $this->locationSuppliers();
        $issuers = $this->issuerCompanies();
        $contracts = $this->completedContracts();
        $approvers = function_exists('cns_eligible_approvers') ? cns_eligible_approvers() : collect();

        $task = trim((string) $request->input('Task', ''));
        $dn = DeliveryNote::where('DN_No', $task)->first();
        if (!$dn && is_numeric($task)) {
            $dn = DeliveryNote::find($task);
        }

        $bookingConfirmations = $this->availableBookingConfirmations($dn->BC_No ?? null);

        if ($dn && !empty($dn->Shipper) && !$issuers->contains('Supplier_ID', $dn->Shipper)) {
            $extra = DB::table('supplier')->where('Supplier_ID', $dn->Shipper)->first();
            if ($extra) {
                $issuers->push($extra);
            }
        }
        if ($dn && !empty($dn->Consignee) && !$companies->contains('Supplier_ID', $dn->Consignee)) {
            $extra = DB::table('supplier')->where('Supplier_ID', $dn->Consignee)->first();
            if ($extra) {
                $companies->push($extra);
            }
        }

        return view('cns.export.dn-detail', compact('companies', 'issuers', 'contracts', 'bookingConfirmations', 'approvers', 'dn'));
    }

    public function detailDeliveryNoteSubmit(Request $request)
    {
        $r = $request->all();
        unset($r['_token']);

        $dn = DeliveryNote::where('DN_No', $r['DN_No'] ?? '')->first();
        if ($dn && $dn->Status === 'APPROVED') {
            echo "Delivery Note sudah di-approve dan tidak dapat diubah";
            return;
        }

        if (array_key_exists('BC_No', $r)) {
            $bcNo = trim((string) $r['BC_No']);
            if ($bcNo !== '') {
                $bcRecord = $this->findBookingConfirmation($bcNo);
                if (!$bcRecord) {
                    return response()->json(['status' => 'error', 'message' => 'Booking Confirmation (BC No) tidak ditemukan pada Vessel Schedule.'], 422);
                }
                $exists = DeliveryNote::where('BC_No', $bcNo)
                    ->where('DN_No', '!=', $r['DN_No'] ?? '')
                    ->exists();
                if ($exists) {
                    return response()->json(['status' => 'error', 'message' => "Delivery Note untuk BC {$bcNo} sudah pernah dibuat."], 422);
                }
                $r['BC_No'] = $bcNo;
                if (empty($r['Contract_No']) && !empty($bcRecord->Contract_No)) {
                    $r['Contract_No'] = $bcRecord->Contract_No;
                }
            } else {
                $r['BC_No'] = null;
            }
        }

        $r = $this->withSignatoryFields($r);
        DeliveryNote::where('DN_No', $r['DN_No'])->update($r);

        echo "success";
    }

    public function approveDeliveryNote(Request $request)
    {
        $user = auth()->user();
        $isAllowed = $user && (
            $user->username === 'admin'
            || in_array($user->Priv_ID ?? '', ['ADM', 'CNS', 'SPV', 'MANAGER'])
            || (method_exists($user, 'hasRole') && ($user->hasRole('ADM') || $user->hasRole('super-admin')))
            || (method_exists($user, 'can') && ($user->can('menu.SI00315.approve') || $user->can('cns.delivery_note.edit')))
        );

        if (!$isAllowed) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses approve Delivery Note.'], 403);
        }

        $task = trim((string) ($request->input('dn_no') ?: $request->input('id') ?: $request->input('Task', '')));
        $dn = DeliveryNote::where('DN_No', $task)->first();
        if (!$dn && is_numeric($task)) {
            $dn = DeliveryNote::find($task);
        }

        if (!$dn) {
            return response()->json(['status' => 'error', 'message' => 'Delivery Note tidak ditemukan.'], 404);
        }

        if ($dn->Status === 'APPROVED') {
            return response()->json(['status' => 'error', 'message' => 'Delivery Note sudah berstatus APPROVED.'], 422);
        }

        if (empty($dn->signatory_key)) {
            $dn->signatory_key = $user->id;
            $dn->signatory_name = $user->Name ?? $user->username;
            $dn->signatory_title = $user->Position ?? '';
        }

        $dn->Status = 'APPROVED';
        $dn->approved_by = $user->id;
        $dn->approved_at = now();
        $dn->save();

        return response()->json([
            'status'  => 'success',
            'message' => "Delivery Note {$dn->DN_No} berhasil di-approve.",
        ]);
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
        $mime = [
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
        ][$extension] ?? 'image/png';

        $data = file_get_contents($fullPath);
        return $data !== false ? 'data:' . $mime . ';base64,' . base64_encode($data) : null;
    }

    private function withSignatoryFields(array $r): array
    {
        $signatoryId = $r['signatory_key'] ?? null;
        if (!empty($signatoryId)) {
            $signatory = User::find($signatoryId);
            $r['signatory_name'] = $signatory ? ($signatory->Name ?? $signatory->username) : ($r['signatory_name'] ?? null);
            $r['signatory_title'] = trim((string) ($r['signatory_title'] ?? '')) ?: ($signatory->Position ?? '');
        } else {
            $r['signatory_key'] = null;
            $r['signatory_name'] = null;
        }

        return $r;
    }

    private function locationSuppliers()
    {
        $query = DB::table('supplier');
        if (auth()->check() && !empty(auth()->user()->Location)) {
            $query->where('Location', auth()->user()->Location);
        }

        return $query->orderBy('Company_Name')->get();
    }

    private function issuerCompanies()
    {
        $query = DB::table('supplier')
            ->where('Is_Consignee', 0)
            ->where('Is_Company', 1)
            ->where('Is_Supplier', 0);

        if (auth()->check() && !empty(auth()->user()->Location)) {
            $query->where('Location', auth()->user()->Location);
        }

        return $query->orderBy('Company_Name')->get();
    }

    private function completedContracts()
    {
        $query = DB::table('cns_buyer_contracts as c')
            ->leftJoin('supplier as s', 's.ID', '=', 'c.Supplier_ID')
            ->where('c.Status', 'COMPLETED');

        if (auth()->check() && !empty(auth()->user()->Location)) {
            $query->where('c.Location', auth()->user()->Location);
        }

        return $query->orderBy('c.Contract_No')
            ->select([
                'c.ID',
                'c.Contract_No',
                'c.Buyer_Name',
                'c.Buyer_Address',
                'c.Quantity_MT',
                DB::raw('s.Supplier_ID as Consignee_Code'),
                DB::raw('s.Address as Supplier_Address'),
            ])
            ->get();
    }

    private function availableBookingConfirmations(?string $includeBcNo = null)
    {
        if (!Schema::hasTable('vessel_schedules') || !Schema::hasColumn('vessel_schedules', 'BC_No')) {
            return collect();
        }

        $query = DB::table('vessel_schedules as vs')
            ->leftJoin('cns_buyer_contracts as c', 'c.ID', '=', 'vs.Contract_ID')
            ->leftJoin('supplier as s', 's.ID', '=', 'c.Supplier_ID')
            ->whereNotNull('vs.BC_No')
            ->where('vs.BC_No', '!=', '');

        if (auth()->check() && !empty(auth()->user()->Location)) {
            $location = auth()->user()->Location;
            $query->where(function ($q) use ($location) {
                $q->where('c.Location', $location)->orWhereNull('c.Location');
            });
        }

        if (Schema::hasTable('delivery_notes') && Schema::hasColumn('delivery_notes', 'BC_No')) {
            $usedBcNos = DeliveryNote::whereNotNull('BC_No')
                ->where('BC_No', '!=', '')
                ->when($includeBcNo !== null && $includeBcNo !== '', function ($q) use ($includeBcNo) {
                    $q->where('BC_No', '!=', $includeBcNo);
                })
                ->pluck('BC_No')
                ->all();

            if (!empty($usedBcNos)) {
                $query->whereNotIn('vs.BC_No', $usedBcNos);
            }
        }

        return $query->groupBy('vs.BC_No')
            ->orderBy('vs.BC_No')
            ->select([
                'vs.BC_No',
                DB::raw('MAX(c.Contract_No) as Contract_No'),
                DB::raw('MAX(c.Buyer_Name) as Buyer_Name'),
                DB::raw('MAX(c.Buyer_Address) as Buyer_Address'),
                DB::raw('MAX(c.Quantity_MT) as Contract_Quantity_MT'),
                DB::raw('SUM(COALESCE(vs.Qty, 0)) as Schedule_Qty_MT'),
                DB::raw('MAX(s.Supplier_ID) as Consignee_Code'),
                DB::raw('MAX(s.Address) as Supplier_Address'),
            ])
            ->get()
            ->map(function ($row) {
                $scheduleQty = (float) ($row->Schedule_Qty_MT ?? 0);
                $row->Quantity_MT = $scheduleQty > 0 ? $scheduleQty : $row->Contract_Quantity_MT;
                return $row;
            });
    }

    private function findBookingConfirmation(string $bcNo)
    {
        if (!Schema::hasTable('vessel_schedules') || !Schema::hasColumn('vessel_schedules', 'BC_No')) {
            return null;
        }

        return DB::table('vessel_schedules as vs')
            ->leftJoin('cns_buyer_contracts as c', 'c.ID', '=', 'vs.Contract_ID')
            ->where('vs.BC_No', $bcNo)
            ->select(['vs.BC_No', 'c.Contract_No'])
            ->first();
    }

    private function isDuplicateEntryException(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? null;
        $driverCode = $e->errorInfo[1] ?? null;
        $message = $e->getMessage();

        return $sqlState === '23000'
            || $driverCode === 1062
            || stripos($message, 'UNIQUE constraint failed') !== false
            || stripos($message, 'Duplicate entry') !== false;
    }
}
