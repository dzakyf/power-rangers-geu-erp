<?php

namespace App\Services\HCGA;

use App\Services\Approval\AssetLifecycleApprovalService;
use App\Services\Approval\ProcurementApprovalService;
use App\Services\GA\TicketService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service terpadu untuk Unified Asset Ticketing & Lifecycle Hub:
 * - DAMAGE (ga_ticket ber-Asset_No & asset_fixing)
 * - PROCUREMENT (purchase_request aset / penggantian)
 * - MOVING (asset_moving)
 * - DISPOSAL (asset_disposal)
 */
class AssetTicketingService
{
    /** @var TicketService */
    protected $ticketService;

    /** @var AssetLifecycleService */
    protected $lifecycleService;

    /** @var AssetLifecycleApprovalService */
    protected $lifecycleApproval;

    /** @var ProcurementApprovalService */
    protected $procurementApproval;

    public function __construct(
        TicketService $ticketService,
        AssetLifecycleService $lifecycleService,
        AssetLifecycleApprovalService $lifecycleApproval,
        ProcurementApprovalService $procurementApproval
    ) {
        $this->ticketService = $ticketService;
        $this->lifecycleService = $lifecycleService;
        $this->lifecycleApproval = $lifecycleApproval;
        $this->procurementApproval = $procurementApproval;
    }

    /**
     * Ambil daftar gabungan 4 kategori tiket/pengajuan aset beserta KPI ringkasan.
     */
    public function getDashboardData(array $filters = []): array
    {
        $typeFilter = strtoupper(trim((string) ($filters['type'] ?? 'ALL')));
        $search = strtolower(trim((string) ($filters['search'] ?? '')));
        $assetNoFilter = trim((string) ($filters['asset_no'] ?? ''));

        $items = collect();

        // 1. DAMAGE: ga_ticket yang terhubung ke Asset_No
        $damageTickets = DB::table('ga_ticket as gt')
            ->join('ticket as t', 't.ID', '=', 'gt.Ticket_ID')
            ->leftJoin('asset_list as a', function ($join) {
                $join->on(
                    DB::raw('CONVERT(gt.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'),
                    '=',
                    DB::raw('CONVERT(a.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci')
                );
            })
            ->whereNotNull('gt.Asset_No')
            ->where('gt.Asset_No', '!=', '')
            ->select(
                't.ID as ref_id',
                't.Ticket_No as doc_no',
                'gt.Asset_No as asset_no',
                'a.Product_Description as asset_name',
                't.Ticket_Subject as title',
                'gt.Damage_Severity as sub_info',
                'gt.Reporter_Name as requester',
                'gt.Location as location',
                't.Ticket_Status as status',
                't.Proposed_Date as doc_date',
                'gt.Created_At as sort_at'
            )
            ->orderBy('t.ID', 'DESC')
            ->limit(150)
            ->get();

        foreach ($damageTickets as $row) {
            $items->push((object) [
                'category'   => 'DAMAGE_TICKET',
                'group_type' => 'DAMAGE',
                'ref_id'     => (int) $row->ref_id,
                'doc_no'     => $row->doc_no,
                'asset_no'   => $row->asset_no,
                'asset_name' => $row->asset_name ?: '-',
                'title'      => $row->title,
                'sub_info'   => $row->sub_info ?: 'RUSAK RINGAN',
                'requester'  => $row->requester ?: '-',
                'location'   => $row->location ?: '-',
                'status'     => strtoupper((string) $row->status),
                'doc_date'   => $row->doc_date,
                'sort_at'    => (string) ($row->sort_at ?? $row->doc_date),
            ]);
        }

        // 1b. DAMAGE_FIXING: asset_fixing
        $fixings = DB::table('asset_fixing as f')
            ->leftJoin('asset_list as a', function ($join) {
                $join->on(
                    DB::raw('CONVERT(f.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'),
                    '=',
                    DB::raw('CONVERT(a.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci')
                );
            })
            ->select(
                'f.ID as ref_id',
                'f.No_Trx as doc_no',
                'f.Asset_No as asset_no',
                'f.Nama_Aset as asset_name',
                'f.Jenis_Perbaikan as title',
                'f.Estimasi_Biaya as est_cost',
                'f.Nama_Pengaju as requester',
                'a.Asset_Location as location',
                'f.Status as status',
                'f.Tanggal_Pengajuan as doc_date',
                'f.created_at as sort_at'
            )
            ->orderBy('f.ID', 'DESC')
            ->limit(150)
            ->get();

        foreach ($fixings as $row) {
            $items->push((object) [
                'category'   => 'ASSET_FIXING',
                'group_type' => 'DAMAGE',
                'ref_id'     => (int) $row->ref_id,
                'doc_no'     => $row->doc_no,
                'asset_no'   => $row->asset_no ?: '-',
                'asset_name' => $row->asset_name ?: '-',
                'title'      => 'Perbaikan: ' . ($row->title ?: '-'),
                'sub_info'   => 'Est. Rp ' . number_format((float) ($row->est_cost ?? 0), 0, ',', '.'),
                'requester'  => $row->requester ?: '-',
                'location'   => $row->location ?: '-',
                'status'     => strtoupper((string) $row->status),
                'doc_date'   => $row->doc_date,
                'sort_at'    => (string) ($row->sort_at ?? $row->doc_date),
            ]);
        }

        // 2. PROCUREMENT: purchase_request kategori ASSET atau memiliki Asset_No
        $procurements = DB::table('purchase_request as pr')
            ->leftJoin('asset_list as a', function ($join) {
                $join->on(
                    DB::raw('CONVERT(pr.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'),
                    '=',
                    DB::raw('CONVERT(a.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci')
                );
            })
            ->where(function ($q) {
                $q->where('pr.Category', 'ASSET')
                    ->orWhere(function ($q2) {
                        $q2->whereNotNull('pr.Asset_No')->where('pr.Asset_No', '!=', '');
                    });
            })
            ->select(
                'pr.ID as ref_id',
                'pr.PR_No as doc_no',
                'pr.Asset_No as asset_no',
                'a.Product_Description as asset_name',
                'pr.Remark as title',
                'pr.Asset_Request_Type as req_type',
                'pr.Created_By as requester',
                'pr.Location as location',
                'pr.Status as status',
                'pr.Created_Date as doc_date'
            )
            ->orderBy('pr.ID', 'DESC')
            ->limit(150)
            ->get();

        foreach ($procurements as $row) {
            $items->push((object) [
                'category'   => 'PROCUREMENT',
                'group_type' => 'PROCUREMENT',
                'ref_id'     => (int) $row->ref_id,
                'doc_no'     => $row->doc_no,
                'asset_no'   => $row->asset_no ?: '-',
                'asset_name' => $row->asset_name ?: 'Pengadaan Aset Baru',
                'title'      => $row->title ?: 'Pengadaan Aset',
                'sub_info'   => ($row->req_type === 'REPLACEMENT') ? 'Penggantian Aset' : 'Pengadaan Baru',
                'requester'  => $row->requester ?: '-',
                'location'   => $row->location ?: '-',
                'status'     => strtoupper((string) $row->status),
                'doc_date'   => $row->doc_date ? substr((string) $row->doc_date, 0, 10) : '-',
                'sort_at'    => (string) ($row->doc_date ?? ''),
            ]);
        }

        // 3. MOVING: moving_asset
        $movings = DB::table('moving_asset as m')
            ->leftJoin('asset_list as a', function ($join) {
                $join->on(
                    DB::raw('CONVERT(m.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci'),
                    '=',
                    DB::raw('CONVERT(a.Asset_No USING utf8mb4) COLLATE utf8mb4_unicode_ci')
                );
            })
            ->select(
                'm.ID as ref_id',
                'm.No_Trx as doc_no',
                'm.Asset_No as asset_no',
                'm.Product_Description as asset_name',
                'm.Moving_Reason as title',
                'm.New_Location as destination',
                'm.UserTrx as requester',
                'a.Asset_Location as location',
                'm.Status as status',
                'm.DateTrx as doc_date',
                'm.DateTrx as sort_at'
            )
            ->orderBy('m.ID', 'DESC')
            ->limit(150)
            ->get();

        foreach ($movings as $row) {
            $items->push((object) [
                'category'   => 'ASSET_MOVING',
                'group_type' => 'MOVING',
                'ref_id'     => (int) $row->ref_id,
                'doc_no'     => $row->doc_no,
                'asset_no'   => $row->asset_no ?: '-',
                'asset_name' => $row->asset_name ?: '-',
                'title'      => $row->title ?: 'Pemindahan Aset',
                'sub_info'   => 'Tujuan: ' . ($row->destination ?: '-'),
                'requester'  => $row->requester ?: '-',
                'location'   => $row->location ?: '-',
                'status'     => strtoupper((string) $row->status),
                'doc_date'   => $row->doc_date,
                'sort_at'    => (string) ($row->sort_at ?? $row->doc_date),
            ]);
        }

        // 4. DISPOSAL: asset_disposal
        $disposals = DB::table('asset_disposal as d')
            ->select(
                'd.ID as ref_id',
                'd.Disposal_No as doc_no',
                DB::raw('COALESCE(d.Asset_No, d.Asset_Serial_No) as asset_no'),
                'd.Asset_Name as asset_name',
                'd.Disposal_Reason as title',
                'd.Book_Value as book_value',
                'd.User_Name as requester',
                'd.Asset_Location as location',
                'd.Status as status',
                'd.Disposal_Date as doc_date',
                'd.Created_Date as sort_at'
            )
            ->orderBy('d.ID', 'DESC')
            ->limit(150)
            ->get();

        foreach ($disposals as $row) {
            $items->push((object) [
                'category'   => 'ASSET_DISPOSAL',
                'group_type' => 'DISPOSAL',
                'ref_id'     => (int) $row->ref_id,
                'doc_no'     => $row->doc_no,
                'asset_no'   => $row->asset_no ?: '-',
                'asset_name' => $row->asset_name ?: '-',
                'title'      => $row->title ?: 'Pemusnahan Aset',
                'sub_info'   => $row->book_value !== null ? 'Nilai Buku: Rp ' . number_format((float) $row->book_value, 0, ',', '.') : 'Penghapusan Aset',
                'requester'  => $row->requester ?: '-',
                'location'   => $row->location ?: '-',
                'status'     => strtoupper((string) $row->status),
                'doc_date'   => $row->doc_date,
                'sort_at'    => (string) ($row->sort_at ?? $row->doc_date),
            ]);
        }

        $closedStatuses = ['CLOSED', 'RESOLVED', 'APPROVED', 'REJECTED', 'CANCELLED', 'COMPLETED'];

        $summary = [
            'damage_open'      => $items->where('group_type', 'DAMAGE')->reject(function ($i) use ($closedStatuses) {
                return in_array($i->status, $closedStatuses, true);
            })->count(),
            'procurement_open' => $items->where('group_type', 'PROCUREMENT')->reject(function ($i) use ($closedStatuses) {
                return in_array($i->status, $closedStatuses, true);
            })->count(),
            'moving_open'      => $items->where('group_type', 'MOVING')->reject(function ($i) use ($closedStatuses) {
                return in_array($i->status, $closedStatuses, true);
            })->count(),
            'disposal_open'    => $items->where('group_type', 'DISPOSAL')->reject(function ($i) use ($closedStatuses) {
                return in_array($i->status, $closedStatuses, true);
            })->count(),
        ];
        $summary['total_active'] = $summary['damage_open'] + $summary['procurement_open'] + $summary['moving_open'] + $summary['disposal_open'];

        $filtered = $items->filter(function ($item) use ($typeFilter, $search, $assetNoFilter) {
            if ($typeFilter !== '' && $typeFilter !== 'ALL' && $item->group_type !== $typeFilter) {
                return false;
            }
            if ($assetNoFilter !== '' && strcasecmp((string) $item->asset_no, $assetNoFilter) !== 0) {
                return false;
            }
            if ($search !== '') {
                $haystack = strtolower(
                    $item->doc_no . ' ' . $item->asset_no . ' ' . $item->asset_name . ' ' .
                    $item->title . ' ' . $item->requester . ' ' . $item->status
                );
                if (strpos($haystack, $search) === false) {
                    return false;
                }
            }
            return true;
        })->sortByDesc('sort_at')->values()->all();

        return [
            'items'   => $filtered,
            'summary' => $summary,
        ];
    }

    /**
     * Buat Tiket Kerusakan Aset melalui ga_ticket (memanfaatkan TicketService).
     */
    public function createDamageTicket(array $data, object $user): object
    {
        $assetNo = trim((string) ($data['Asset_No'] ?? ''));
        if ($assetNo === '') {
            throw new InvalidArgumentException('Asset No wajib dipilih untuk tiket kerusakan aset.');
        }

        $asset = $this->assertActiveAsset($assetNo);
        $severity = strtoupper(trim((string) ($data['Damage_Severity'] ?? 'RUSAK RINGAN')));
        if (!in_array($severity, ['RUSAK RINGAN', 'RUSAK BERAT'], true)) {
            $severity = 'RUSAK RINGAN';
        }

        $categoryId = !empty($data['Category_ID']) ? (int) $data['Category_ID'] : $this->resolveDefaultAssetCategoryId();
        $subject = trim((string) ($data['Ticket_Subject'] ?? ''));
        if ($subject === '') {
            $subject = 'Kerusakan Aset: ' . $asset->Asset_No . ' - ' . ($asset->Product_Description ?? 'Aset');
        }

        $payload = [
            'Location'        => $asset->Asset_Location ?: ($user->Location ?? 'GEU-HO'),
            'Company_ID'      => $user->Company_ID ?? 'GEU',
            'Category_ID'     => $categoryId,
            'Priority'        => $severity === 'RUSAK BERAT' ? 'HIGH' : strtoupper(trim((string) ($data['Priority'] ?? 'NORMAL'))),
            'Source'          => strtoupper(trim((string) ($data['Source'] ?? 'FORM'))),
            'Ticket_Subject'  => $subject,
            'Description'     => trim((string) ($data['Description'] ?? 'Laporan kerusakan barang aset ' . $asset->Asset_No)),
            'Asset_No'        => $asset->Asset_No,
            'Damage_Severity' => $severity,
        ];

        return $this->ticketService->create($payload, $user);
    }

    /**
     * Buat Pengajuan Pengadaan Aset (Baru atau Penggantian) ke purchase_request + ApprovalWorkflowService.
     */
    public function createProcurementRequest(array $data, object $user): array
    {
        $reqType = strtoupper(trim((string) ($data['Asset_Request_Type'] ?? 'NEW')));
        if (!in_array($reqType, ['NEW', 'REPLACEMENT'], true)) {
            $reqType = 'NEW';
        }

        $assetNo = trim((string) ($data['Asset_No'] ?? ''));
        if ($reqType === 'REPLACEMENT' && $assetNo === '') {
            throw new InvalidArgumentException('Asset No wajib diisi untuk pengadaan penggantian aset.');
        }

        $itemName = trim((string) ($data['Item_Name'] ?? ''));
        if ($itemName === '') {
            throw new InvalidArgumentException('Nama barang pengadaan wajib diisi.');
        }

        $qty = max(1, (float) ($data['Qty'] ?? 1));
        $estPrice = max(0, (float) ($data['Est_Price'] ?? 0));
        $totalBudget = $qty * $estPrice;
        $location = trim((string) ($data['Location'] ?? ($user->Location ?? 'GEU-HO')));

        $prId = DB::transaction(function () use ($data, $user, $reqType, $assetNo, $itemName, $qty, $estPrice, $totalBudget, $location) {
            $counter = DB::table('purchase_request')->whereYear('Created_Date', date('Y'))->count() + 1;
            $prNo = 'PR/' . $location . '/' . date('Ym') . '/' . str_pad((string) $counter, 4, '0', STR_PAD_LEFT);

            $id = DB::table('purchase_request')->insertGetId([
                'PR_No'              => $prNo,
                'Category'           => 'ASSET',
                'Asset_No'           => $assetNo !== '' ? $assetNo : null,
                'Asset_Request_Type' => $reqType,
                'Location'           => $location,
                'Department'         => $user->Department ?? 'HCGA',
                'Estimated_Budget'   => $totalBudget,
                'Remark'             => trim((string) ($data['Remark'] ?? ($reqType === 'REPLACEMENT' ? 'Penggantian aset ' . $assetNo : 'Pengadaan aset baru: ' . $itemName))),
                'Status'             => 'CREATED',
                'Created_By'         => $user->username ?? 'system',
                'Created_Date'       => now(),
            ]);

            if (DB::getSchemaBuilder()->hasTable('purchase_request_dtl')) {
                DB::table('purchase_request_dtl')->insert([
                    'PR_No'             => $prNo,
                    'Purchase_Category' => 'ASSET',
                    'Category'          => 'ASSET',
                    'Product_Desc'      => $itemName,
                    'Qty'               => $qty,
                    'Unit'              => trim((string) ($data['Unit'] ?? 'UNIT')),
                    'Default_Price'     => $estPrice,
                    'Prices'            => $totalBudget,
                    'Location'          => $location,
                    'Remark'            => trim((string) ($data['Specification'] ?? '')),
                ]);
            }

            return $id;
        });

        $prRow = DB::table('purchase_request')->where('ID', $prId)->first();
        $approvalReq = $this->procurementApproval->submit($prRow, $user);

        return ['id' => $prId, 'pr_no' => $prRow->PR_No, 'approval_request_id' => $approvalReq->ID ?? null];
    }

    /**
     * Buat Pengajuan Pemindahan Aset ke asset_moving + ApprovalWorkflowService.
     */
    public function createMovingRequest(array $data, object $user): array
    {
        $assetNo = trim((string) ($data['Asset_No'] ?? ''));
        $asset = $this->assertActiveAsset($assetNo);

        $pending = DB::table('moving_asset')
            ->where('Asset_No', $asset->Asset_No)
            ->whereNotIn('Status', ['APPROVED', 'REJECTED', 'CANCELLED'])
            ->exists();
        if ($pending) {
            throw new InvalidArgumentException('Aset ' . $asset->Asset_No . ' masih memiliki pengajuan pemindahan yang aktif.');
        }

        $destination = trim((string) ($data['Moving_Destination'] ?? ''));
        $reason = trim((string) ($data['Moving_Reason'] ?? ''));
        if ($destination === '' || $reason === '') {
            throw new InvalidArgumentException('Lokasi tujuan dan alasan pemindahan wajib diisi.');
        }

        $movingId = DB::transaction(function () use ($data, $user, $asset, $destination, $reason) {
            $counter = DB::table('moving_asset')->whereYear('DateTrx', date('Y'))->count() + 1;
            $reqNo = 'MOV/' . date('Ym') . '/' . str_pad((string) $counter, 4, '0', STR_PAD_LEFT);

            return DB::table('moving_asset')->insertGetId([
                'No_Trx'               => $reqNo,
                'DateTrx'              => now(),
                'User_NIP'             => $user->Emp_ID ?? $user->username,
                'UserTrx'              => $user->username ?? 'system',
                'Department'           => $user->Department ?? 'HCGA',
                'Asset_No'             => $asset->Asset_No,
                'Product_Description'  => $asset->Product_Description ?? $asset->Asset_No,
                'SN'                   => $asset->SN ?? '',
                'Asset_Description'    => trim(($asset->Product_Brand ?? '') . ' ' . ($asset->Model ?? '')),
                'Current_Location'     => $asset->Asset_Location ?? '-',
                'Moving_Reason'        => $reason,
                'New_Location'         => $destination,
                'Moving_Date'          => $data['Moving_Date'] ?? date('Y-m-d'),
                'Receipent_Name'       => trim((string) ($data['Receipent_Name'] ?? '')),
                'Receipent_Department' => trim((string) ($data['Receipent_Department'] ?? '')),
                'Status'               => 'APPROVAL_UPPER',
            ]);
        });

        $moving = DB::table('moving_asset')->where('ID', $movingId)->first();
        $approvalReq = $this->lifecycleApproval->submit(
            AssetLifecycleApprovalService::TYPE_ASSET_MOVING,
            $movingId,
            (string) $moving->No_Trx,
            'Pemindahan Aset ' . $asset->Asset_No . ' ke ' . $destination,
            (int) $user->id,
            ['Asset_No' => $asset->Asset_No, 'Destination' => $destination]
        );

        return ['id' => $movingId, 'request_no' => $moving->No_Trx, 'approval_request_id' => $approvalReq->ID ?? null];
    }

    /**
     * Buat Pengajuan Pemusnahan Aset ke asset_disposal + ApprovalWorkflowService.
     */
    public function createDisposalRequest(array $data, object $user): array
    {
        $assetNo = trim((string) ($data['Asset_No'] ?? ''));
        $asset = $this->assertActiveAsset($assetNo);

        $pending = DB::table('asset_disposal')
            ->where(function ($q) use ($asset) {
                $q->where('Asset_No', $asset->Asset_No)
                    ->orWhere('Asset_Serial_No', $asset->Asset_No);
            })
            ->whereNotIn('Status', ['APPROVED', 'REJECTED', 'CANCELLED'])
            ->exists();
        if ($pending) {
            throw new InvalidArgumentException('Aset ' . $asset->Asset_No . ' masih memiliki pengajuan pemusnahan yang aktif.');
        }

        $reason = trim((string) ($data['Disposal_Reason'] ?? ''));
        if ($reason === '') {
            throw new InvalidArgumentException('Alasan pemusnahan wajib diisi.');
        }

        $bookValue = isset($data['Book_Value']) && $data['Book_Value'] !== ''
            ? (float) $data['Book_Value']
            : (float) ($asset->Buy_Val ?? 0);

        $disposalId = DB::transaction(function () use ($data, $user, $asset, $reason, $bookValue) {
            $counter = DB::table('asset_disposal')->whereYear('Disposal_Date', date('Y'))->count() + 1;
            $disposalNo = 'DSP/' . date('Ym') . '/' . str_pad((string) $counter, 4, '0', STR_PAD_LEFT);

            return DB::table('asset_disposal')->insertGetId([
                'Disposal_No'          => $disposalNo,
                'Disposal_Date'        => date('Y-m-d'),
                'User_NIK'             => $user->Emp_ID ?? null,
                'User_Name'            => $user->Name ?? $user->username,
                'User_Position'        => $user->Position ?? '',
                'Asset_Name'           => $asset->Product_Description ?? $asset->Asset_No,
                'Asset_No'             => $asset->Asset_No,
                'Asset_Serial_No'      => $asset->SN ?: $asset->Asset_No,
                'Asset_Description'    => trim(($asset->Product_Brand ?? '') . ' ' . ($asset->Model ?? '')),
                'Asset_Condition'      => $asset->Kondisi_Aset ?? 'RUSAK BERAT',
                'Asset_Location'       => $asset->Asset_Location ?? '-',
                'Disposal_Reason'      => $reason,
                'Disposal_Actual_Date' => $data['Disposal_Actual_Date'] ?? date('Y-m-d'),
                'Book_Value'           => $bookValue,
                'Status'               => 'WAITING_APPROVAL',
                'Created_By'           => $user->username ?? 'system',
                'Created_Date'         => now(),
            ]);
        });

        $disposal = DB::table('asset_disposal')->where('ID', $disposalId)->first();
        $approvalReq = $this->lifecycleApproval->submit(
            AssetLifecycleApprovalService::TYPE_ASSET_DISPOSAL,
            $disposalId,
            (string) $disposal->Disposal_No,
            'Pemusnahan Aset ' . $asset->Asset_No . ' (' . ($asset->Product_Description ?? '-') . ')',
            (int) $user->id,
            ['Asset_No' => $asset->Asset_No, 'Amount' => $bookValue, 'Book_Value' => $bookValue]
        );

        return ['id' => $disposalId, 'disposal_no' => $disposal->Disposal_No, 'approval_request_id' => $approvalReq->ID ?? null];
    }

    /**
     * Eskalasi 1-klik dari Tiket Kerusakan (ga_ticket) ke Perbaikan (asset_fixing) atau Pengadaan Pengganti.
     */
    public function escalateDamageTicket(int $ticketId, string $targetType, array $data, object $user): array
    {
        $ticket = $this->ticketService->find($ticketId);
        if (!$ticket || empty($ticket->Asset_No)) {
            throw new InvalidArgumentException('Tiket kerusakan aset tidak ditemukan atau tidak memiliki Asset No.');
        }

        $asset = $this->assertActiveAsset((string) $ticket->Asset_No);
        $targetType = strtoupper(trim($targetType));

        if ($targetType === 'REPLACEMENT') {
            return $this->createProcurementRequest([
                'Asset_Request_Type' => 'REPLACEMENT',
                'Asset_No'           => $asset->Asset_No,
                'Item_Name'          => $data['Item_Name'] ?? ($asset->Product_Description ?: 'Pengganti ' . $asset->Asset_No),
                'Qty'                => $data['Qty'] ?? 1,
                'Est_Price'          => $data['Est_Price'] ?? ($asset->Buy_Val ?? 0),
                'Remark'             => 'Penggantian aset rusak dari Tiket ' . $ticket->Ticket_No . ': ' . ($data['Remark'] ?? $ticket->Ticket_Subject),
            ], $user);
        }

        if ($targetType === 'FIXING') {
            $estCost = max(0, (float) ($data['Estimasi_Biaya'] ?? 0));
            $fixingId = DB::transaction(function () use ($ticket, $asset, $estCost, $data, $user) {
                $counter = DB::table('asset_fixing')->whereYear('Tanggal_Pengajuan', date('Y'))->count() + 1;
                $reqNo = 'FIX/' . date('Ym') . '/' . str_pad((string) $counter, 4, '0', STR_PAD_LEFT);

                return DB::table('asset_fixing')->insertGetId([
                    'No_Trx'            => $reqNo,
                    'Ticket_No'         => $ticket->Ticket_No,
                    'Jenis_Perbaikan'   => $data['Jenis_Perbaikan'] ?? 'KOREKTIF',
                    'Nama_Pengaju'      => $user->Name ?? $user->username,
                    'Jabatan'           => $user->Position ?? '',
                    'Department'        => $user->Department ?? 'HCGA',
                    'Tanggal_Pengajuan' => date('Y-m-d'),
                    'Nama_Aset'         => $asset->Product_Description ?? $asset->Asset_No,
                    'Product_Code'      => $asset->Product_Code ?? '',
                    'Asset_No'          => $asset->Asset_No,
                    'Nomor_Seri'        => $asset->SN ?? '',
                    'Deskripsi_Aset'    => $asset->Product_Description ?? '',
                    'Kondisi_Aset'      => $asset->Kondisi_Aset ?? 'RUSAK RINGAN',
                    'Lokasi_Saat_Ini'   => $asset->Asset_Location ?? '-',
                    'Estimasi_Biaya'    => $estCost,
                    'Alasan_Perkiraan'  => $data['Keterangan'] ?? ('Eskalasi dari tiket ' . $ticket->Ticket_No . ': ' . $ticket->Ticket_Subject),
                    'Status'            => 'WAITING_APPROVAL',
                    'User_NIP'          => $user->Emp_ID ?? $user->username,
                    'User_Trx'          => $user->username ?? 'system',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            });

            $fixing = DB::table('asset_fixing')->where('ID', $fixingId)->first();
            $approvalReq = $this->lifecycleApproval->submit(
                AssetLifecycleApprovalService::TYPE_ASSET_FIXING,
                $fixingId,
                (string) $fixing->No_Trx,
                'Perbaikan Aset ' . $asset->Asset_No . ' (Ref Tiket ' . $ticket->Ticket_No . ')',
                (int) $user->id,
                ['Asset_No' => $asset->Asset_No, 'Amount' => $estCost, 'Ticket_No' => $ticket->Ticket_No]
            );

            $this->lifecycleService->markInMaintenance($asset->Asset_No, (string) ($user->username ?? 'system'));

            return ['id' => $fixingId, 'request_no' => $fixing->No_Trx, 'approval_request_id' => $approvalReq->ID ?? null];
        }

        throw new InvalidArgumentException('Target eskalasi tidak valid.');
    }

    /**
     * Ambil detail item terpadu beserta timeline approval_request.
     */
    public function getDetail(string $category, int $id): ?array
    {
        $category = strtoupper(trim($category));
        $row = null;
        $assetNo = null;
        $approvalRequestId = null;

        if ($category === 'DAMAGE_TICKET') {
            $row = DB::table('ga_ticket as gt')
                ->join('ticket as t', 't.ID', '=', 'gt.Ticket_ID')
                ->where('t.ID', $id)
                ->select('t.*', 'gt.Asset_No', 'gt.Damage_Severity', 'gt.Reporter_Name', 'gt.Location as Ga_Location')
                ->first();
            $assetNo = $row->Asset_No ?? null;
        } elseif ($category === 'ASSET_FIXING') {
            $row = DB::table('asset_fixing')->where('ID', $id)->first();
            $assetNo = $row->Asset_No ?? null;
            $approvalRequestId = $row->Approval_Request_ID ?? null;
        } elseif ($category === 'PROCUREMENT') {
            $row = DB::table('purchase_request')->where('ID', $id)->first();
            $assetNo = $row->Asset_No ?? null;
            $approvalRequestId = $row->Approval_Request_ID ?? null;
        } elseif ($category === 'ASSET_MOVING') {
            $row = DB::table('moving_asset')->where('ID', $id)->first();
            $assetNo = $row->Asset_No ?? null;
            $approvalRequestId = $row->Approval_Request_ID ?? null;
        } elseif ($category === 'ASSET_DISPOSAL') {
            $row = DB::table('asset_disposal')->where('ID', $id)->first();
            $assetNo = $row ? (($row->Asset_No ?? '') ?: ($row->Asset_Serial_No ?? null)) : null;
            $approvalRequestId = $row->Approval_Request_ID ?? null;
        }

        if (!$row) {
            return null;
        }

        $asset = $assetNo ? DB::table('asset_list')->where('Asset_No', $assetNo)->first() : null;
        $timeline = $approvalRequestId ? $this->lifecycleApproval->getRequestWithTimeline((int) $approvalRequestId) : null;

        return [
            'category' => $category,
            'item'     => $row,
            'asset'    => $asset,
            'timeline' => $timeline,
        ];
    }

    protected function assertActiveAsset(string $assetNo): object
    {
        $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
        if (!$asset) {
            throw new InvalidArgumentException('Aset ' . $assetNo . ' tidak ditemukan di master asset_list.');
        }
        if (strtoupper((string) ($asset->Status ?? '')) === 'DISPOSED') {
            throw new InvalidArgumentException('Aset ' . $assetNo . ' sudah berstatus DISPOSED dan tidak dapat diproses lagi.');
        }
        return $asset;
    }

    protected function resolveDefaultAssetCategoryId(): ?int
    {
        $cat = DB::table('ga_ticket_category')
            ->where('Status', 'ACTIVE')
            ->where(function ($q) {
                $q->where('Name', 'LIKE', '%Asset%')
                    ->orWhere('Name', 'LIKE', '%Aset%')
                    ->orWhere('Name', 'LIKE', '%Perbaikan%')
                    ->orWhere('Name', 'LIKE', '%Maintenance%');
            })
            ->orderBy('ID', 'ASC')
            ->first();

        if ($cat) {
            return (int) $cat->ID;
        }

        $fallback = DB::table('ga_ticket_category')->where('Status', 'ACTIVE')->orderBy('ID', 'ASC')->first();
        return $fallback ? (int) $fallback->ID : null;
    }
}
