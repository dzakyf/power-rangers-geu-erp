<?php

namespace App\Http\Controllers\CNS;

use App\BuyerContractBooking;
use App\CnsCustomsClearance;
use App\Http\Controllers\Controller;
use App\Support\DhtmlxGridPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CustomsClearanceController extends Controller
{
    private const UPLOAD_SUBDIR = 'uploads/customs-clearance';

    public function index()
    {
        return view('cns.export.customs-clearance');
    }

    public function indexXml(Request $request)
    {
        $paginated = $this->paginateBookings($request);
        $bookings = collect($paginated['rows']);
        $clearances = $bookings->isEmpty()
            ? collect()
            : $this->fetchClearancesByBookingIds($bookings->pluck('ID')->all());

        $bodyXml = '';
        $nomor = $paginated['posStart'] + 1;

        foreach ($bookings as $booking) {
            $clearance = $clearances->get($booking->ID);
            $contractNo = $booking->contract ? $booking->contract->Contract_No : '-';
            $uploadedCount = $clearance ? $clearance->uploadedCount() : 0;
            $notes = $clearance ? ($clearance->notes ?: '') : '';

            $bodyXml .= '<row id="' . (int) $booking->ID . '">';
            $bodyXml .= '<cell>' . ($nomor++) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $contractNo, ENT_QUOTES, 'UTF-8') . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $booking->Booking_No, ENT_QUOTES, 'UTF-8') . '</cell>';
            foreach (array_keys(CnsCustomsClearance::DOC_TYPES) as $docType) {
                $bodyXml .= '<cell>' . $this->formatDocCell($clearance, $docType) . '</cell>';
            }
            $bodyXml .= '<cell>' . $uploadedCount . '/4</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $notes, ENT_QUOTES, 'UTF-8') . '</cell>';
            $bodyXml .= "</row>\n";
        }

        return DhtmlxGridPagination::response($paginated['total'], $paginated['posStart'], $bodyXml);
    }

    public function detail(Request $request)
    {
        $id = $request->input('Task');
        $booking = $this->findBookingWithContractOrFail($id);
        $clearance = $this->findOrNewClearance((int) $booking->ID);

        return view('cns.export.customs-clearance-detail', [
            'booking'       => $booking,
            'contract'      => $booking->contract,
            'clearance'     => $clearance,
            'docTypes'      => CnsCustomsClearance::DOC_TYPES,
            'uploadedCount' => $clearance->uploadedCount(),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'integer', $this->bookingExistsRule()],
            'notes'      => 'nullable|string|max:2000',
            'doc_3d'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'peb'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'npe'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'sphib'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $clearance = $this->findOrNewClearance((int) $validated['booking_id']);
        $uploadDir = public_path(self::UPLOAD_SUBDIR);
        $oldFilesToDelete = [];

        foreach (array_keys(CnsCustomsClearance::DOC_TYPES) as $docType) {
            if (!$request->hasFile($docType)) {
                continue;
            }

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $file = $request->file($docType);
            $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
            $filename = $docType . '_' . (int) $validated['booking_id'] . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

            $oldPath = $clearance->{"{$docType}_path"};
            $file->move($uploadDir, $filename);

            $clearance->{"{$docType}_path"} = self::UPLOAD_SUBDIR . '/' . $filename;
            $clearance->{"{$docType}_name"} = $file->getClientOriginalName();

            if (!empty($oldPath)) {
                $oldFilesToDelete[] = $oldPath;
            }
        }

        if ($request->exists('notes')) {
            $clearance->notes = $validated['notes'] ?? null;
        }

        $clearance->updated_by = Auth::check()
            ? (Auth::user()->username ?: Auth::user()->name)
            : 'system';
        $this->persistClearance($clearance);

        foreach ($oldFilesToDelete as $oldPath) {
            $this->deleteStoredFile($oldPath);
        }

        return response()->json([
            'status'         => 'success',
            'message'        => 'Dokumen Customs Clearance berhasil disimpan.',
            'uploaded_count' => $clearance->uploadedCount(),
            'data'           => $clearance,
        ]);
    }

    public function file($id, $docType)
    {
        abort_unless(array_key_exists($docType, CnsCustomsClearance::DOC_TYPES), 404);

        $clearance = $this->findClearanceByIdOrFail($id);
        $relativePath = (string) $clearance->{"{$docType}_path"};
        abort_if($relativePath === '', 404);

        $path = $this->resolveSafeUploadPath($relativePath);
        abort_if($path === null, 404);

        $filename = $clearance->{"{$docType}_name"} ?: basename($path);
        $safeFilename = str_replace(['"', "\r", "\n", '\\', '/'], '', $filename);

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . $safeFilename . '"',
        ]);
    }

    protected function paginateBookings(Request $request): array
    {
        $query = BuyerContractBooking::with('contract');

        if ($request->filled('tgl1') && $request->filled('tgl2')) {
            $query->whereBetween('Created_Date', [
                $request->input('tgl1') . ' 00:00:00',
                $request->input('tgl2') . ' 23:59:59',
            ]);
        }

        if (!$request->filled('sort_col')) {
            $query->orderByDesc('ID');
        }

        return DhtmlxGridPagination::paginateQuery($query, $request, [
            'col_map' => [
                2 => 'Booking_No',
            ],
            'default_page_size' => 25,
        ]);
    }

    protected function fetchClearancesByBookingIds(array $bookingIds): Collection
    {
        return CnsCustomsClearance::whereIn('booking_id', $bookingIds)
            ->get()
            ->keyBy('booking_id');
    }

    protected function findBookingWithContractOrFail($id): BuyerContractBooking
    {
        return BuyerContractBooking::with('contract')->findOrFail($id);
    }

    protected function findOrNewClearance(int $bookingId): CnsCustomsClearance
    {
        return CnsCustomsClearance::firstOrNew(['booking_id' => $bookingId]);
    }

    protected function persistClearance(CnsCustomsClearance $clearance): void
    {
        $clearance->save();
    }

    protected function findClearanceByIdOrFail($id): CnsCustomsClearance
    {
        return CnsCustomsClearance::findOrFail($id);
    }

    protected function bookingExistsRule(): string
    {
        return 'exists:cns_buyer_contract_bookings,ID';
    }

    private function formatDocCell(?CnsCustomsClearance $clearance, string $docType): string
    {
        if (!$clearance || empty($clearance->{"{$docType}_path"})) {
            return '-';
        }

        $url = url("cns-export-customs-clearance-file/{$clearance->id}/{$docType}");
        $name = $clearance->{"{$docType}_name"} ?: basename((string) $clearance->{"{$docType}_path"});

        return htmlspecialchars($url . '^' . $name, ENT_QUOTES, 'UTF-8');
    }

    private function resolveSafeUploadPath(string $relativePath): ?string
    {
        $root = realpath(public_path(self::UPLOAD_SUBDIR));
        $path = realpath(public_path(ltrim($relativePath, '/')));

        if (
            !$root
            || !$path
            || strpos($path, $root . DIRECTORY_SEPARATOR) !== 0
            || !is_file($path)
        ) {
            return null;
        }

        return $path;
    }

    private function deleteStoredFile(string $relativePath): void
    {
        $path = $this->resolveSafeUploadPath($relativePath);
        if ($path !== null) {
            @unlink($path);
        }
    }
}
