<?php

namespace App\Services\CNS;

use App\CnsGoodsReceipt;
use App\CnsGoodsReceiptDetail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Exception;

class GoodsReceiptService
{
    protected $inventoryService;

    public function __construct(NonUcoInventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Generate the next Goods Receipt number.
     * Format: GR/CNS/YYYYMM/0001
     */
    public function generateGrNo(?string $date = null): string
    {
        $dateObj = $date ? \Carbon\Carbon::parse($date) : now();
        $prefix = 'GR/CNS/' . $dateObj->format('Ym') . '/';

        $latest = CnsGoodsReceipt::where('gr_no', 'LIKE', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $seq = 1;
        if ($latest && preg_match('/\/(\d+)$/', $latest->gr_no, $matches)) {
            $seq = ((int) $matches[1]) + 1;
        }

        return $prefix . sprintf('%04d', $seq);
    }

    /**
     * Create draft Goods Receipt.
     */
    public function createDraft(array $data, ?UploadedFile $attachment = null, ?string $actor = null): CnsGoodsReceipt
    {
        $user = $actor ?: (auth()->check() ? auth()->user()->username : 'admin');
        $grNo = $this->generateGrNo($data['receipt_date'] ?? null);

        $attachmentPath = null;
        if ($attachment && $attachment->isValid()) {
            $dir = public_path('uploads/goods-receipt');
            if (!file_exists($dir)) {
                @mkdir($dir, 0777, true);
            }
            $filename = 'gr_' . time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '', $attachment->getClientOriginalName());
            $attachment->move($dir, $filename);
            $attachmentPath = 'uploads/goods-receipt/' . $filename;
        }

        return DB::transaction(function () use ($data, $grNo, $attachmentPath, $user) {
            $gr = CnsGoodsReceipt::create([
                'gr_no'            => $grNo,
                'po_no'            => $data['po_no'],
                'receipt_date'     => $data['receipt_date'] ?? now()->toDateString(),
                'delivery_note_no' => $data['delivery_note_no'] ?? null,
                'driver_name'      => $data['driver_name'] ?? null,
                'vehicle_no'       => $data['vehicle_no'] ?? null,
                'received_by'      => $data['received_by'] ?? $user,
                'status'           => 'DRAFT',
                'attachment_path'  => $attachmentPath,
                'notes'            => $data['notes'] ?? null,
                'created_by'       => $user,
            ]);

            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $qtyReceived = (float) ($item['qty_received'] ?? 0);
                    $qtyOrdered  = (float) ($item['qty_ordered'] ?? 0);

                    CnsGoodsReceiptDetail::create([
                        'goods_receipt_id' => $gr->id,
                        'po_dtl_id'        => $item['po_dtl_id'] ?? null,
                        'item_code'        => $item['item_code'] ?? null,
                        'item_name'        => $item['item_name'] ?? 'Item Non-UCO',
                        'category'         => $item['category'] ?? 'General',
                        'unit'             => $item['unit'] ?? 'PCS',
                        'qty_ordered'      => $qtyOrdered,
                        'qty_received'     => $qtyReceived,
                        'notes'            => $item['notes'] ?? null,
                    ]);
                }
            }

            return $gr;
        });
    }

    /**
     * Update draft Goods Receipt.
     */
    public function updateDraft(int $id, array $data, ?UploadedFile $attachment = null, ?string $actor = null): CnsGoodsReceipt
    {
        $gr = CnsGoodsReceipt::with('details')->findOrFail($id);

        if ($gr->status !== 'DRAFT') {
            throw new Exception("Hanya dokumen DRAFT yang dapat diedit.");
        }

        $user = $actor ?: (auth()->check() ? auth()->user()->username : 'admin');

        $attachmentPath = $gr->attachment_path;
        if ($attachment && $attachment->isValid()) {
            $dir = public_path('uploads/goods-receipt');
            if (!file_exists($dir)) {
                @mkdir($dir, 0777, true);
            }
            $filename = 'gr_' . time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '', $attachment->getClientOriginalName());
            $attachment->move($dir, $filename);
            $attachmentPath = 'uploads/goods-receipt/' . $filename;
        }

        return DB::transaction(function () use ($gr, $data, $attachmentPath, $user) {
            $gr->update([
                'receipt_date'     => $data['receipt_date'] ?? $gr->receipt_date,
                'delivery_note_no' => $data['delivery_note_no'] ?? $gr->delivery_note_no,
                'driver_name'      => $data['driver_name'] ?? $gr->driver_name,
                'vehicle_no'       => $data['vehicle_no'] ?? $gr->vehicle_no,
                'received_by'      => $data['received_by'] ?? $gr->received_by,
                'attachment_path'  => $attachmentPath,
                'notes'            => $data['notes'] ?? $gr->notes,
            ]);

            if (isset($data['items']) && is_array($data['items'])) {
                $gr->details()->delete();

                foreach ($data['items'] as $item) {
                    CnsGoodsReceiptDetail::create([
                        'goods_receipt_id' => $gr->id,
                        'po_dtl_id'        => $item['po_dtl_id'] ?? null,
                        'item_code'        => $item['item_code'] ?? null,
                        'item_name'        => $item['item_name'] ?? 'Item Non-UCO',
                        'category'         => $item['category'] ?? 'General',
                        'unit'             => $item['unit'] ?? 'PCS',
                        'qty_ordered'      => (float) ($item['qty_ordered'] ?? 0),
                        'qty_received'     => (float) ($item['qty_received'] ?? 0),
                        'notes'            => $item['notes'] ?? null,
                    ]);
                }
            }

            return $gr->fresh('details');
        });
    }

    /**
     * Approve Goods Receipt.
     * Posts stock mutation (IN) and updates PO receiving status.
     */
    public function approve(int $id, ?string $actor = null): CnsGoodsReceipt
    {
        $gr = CnsGoodsReceipt::with('details')->findOrFail($id);

        if ($gr->status !== 'DRAFT') {
            throw new Exception("Hanya dokumen DRAFT yang dapat disetujui.");
        }

        $user = $actor ?: (auth()->check() ? auth()->user()->username : 'admin');

        return DB::transaction(function () use ($gr, $user) {
            $gr->status = 'APPROVED';
            $gr->approved_by = $user;
            $gr->approved_at = now();
            $gr->save();

            // Process stock mutation and update PO detail for each item
            foreach ($gr->details as $detail) {
                if ($detail->qty_received > 0) {
                    $refText = $gr->gr_no . ' (' . $gr->po_no . ')';
                    $noteText = 'Penerimaan GR ' . $gr->gr_no . ' (SJ: ' . ($gr->delivery_note_no ?: '-') . ')';

                    $this->inventoryService->recordStockMutation(
                        $detail->item_name,
                        $detail->category ?: 'General',
                        $detail->unit ?: 'PCS',
                        'IN',
                        (float) $detail->qty_received,
                        $refText,
                        $noteText,
                        $user,
                        null,
                        $gr->id,
                        $detail->item_code,
                        $gr->receipt_date ? $gr->receipt_date->toDateString() : now()->toDateString()
                    );

                    // Update PO Detail if linked
                    if ($detail->po_dtl_id) {
                        $poDtl = DB::table('purchase_order_cns_dtl')->where('ID', $detail->po_dtl_id)->first();
                        if ($poDtl) {
                            $newReceived = (float) ($poDtl->Qty_Receive ?? 0) + (float) $detail->qty_received;
                            $ordered = (float) ($poDtl->Qty ?? 0);
                            $newRemain = max(0, $ordered - $newReceived);

                            DB::table('purchase_order_cns_dtl')->where('ID', $detail->po_dtl_id)->update([
                                'Qty_Receive' => $newReceived,
                                'Qty_Remain'  => $newRemain,
                                'GR_No'       => $gr->gr_no,
                            ]);
                        }
                    }
                }
            }

            // Sync parent purchase_order_cns status
            $this->syncPoReceiveStatus($gr->po_no, $gr->gr_no, $gr->receipt_date ? $gr->receipt_date->toDateString() : now()->toDateString());

            return $gr->fresh('details');
        });
    }

    /**
     * Void an approved Goods Receipt.
     * Creates reversing stock mutation (OUT) and reverts PO received quantities.
     */
    public function void(int $id, ?string $actor = null, ?string $reason = null): CnsGoodsReceipt
    {
        $gr = CnsGoodsReceipt::with('details')->findOrFail($id);

        if ($gr->status !== 'APPROVED') {
            throw new Exception("Hanya dokumen APPROVED yang dapat di-void.");
        }

        $user = $actor ?: (auth()->check() ? auth()->user()->username : 'admin');
        $voidReason = $reason ?: 'Pembatalan transaksi';

        return DB::transaction(function () use ($gr, $user, $voidReason) {
            $gr->status = 'VOID';
            $gr->notes = ($gr->notes ? $gr->notes . ' | ' : '') . "[VOID: {$voidReason} by {$user} at " . now()->toDateTimeString() . "]";
            $gr->save();

            // Reverse stock mutations and PO detail quantities
            foreach ($gr->details as $detail) {
                if ($detail->qty_received > 0) {
                    $refText = 'VOID-' . $gr->gr_no;
                    $noteText = 'Pembatalan (VOID) GR ' . $gr->gr_no . ': ' . $voidReason;

                    $this->inventoryService->recordStockMutation(
                        $detail->item_name,
                        $detail->category ?: 'General',
                        $detail->unit ?: 'PCS',
                        'OUT',
                        (float) $detail->qty_received,
                        $refText,
                        $noteText,
                        $user,
                        null,
                        $gr->id,
                        $detail->item_code,
                        now()->toDateString()
                    );

                    // Revert PO Detail
                    if ($detail->po_dtl_id) {
                        $poDtl = DB::table('purchase_order_cns_dtl')->where('ID', $detail->po_dtl_id)->first();
                        if ($poDtl) {
                            $newReceived = max(0, (float) ($poDtl->Qty_Receive ?? 0) - (float) $detail->qty_received);
                            $ordered = (float) ($poDtl->Qty ?? 0);
                            $newRemain = max(0, $ordered - $newReceived);

                            DB::table('purchase_order_cns_dtl')->where('ID', $detail->po_dtl_id)->update([
                                'Qty_Receive' => $newReceived,
                                'Qty_Remain'  => $newRemain,
                            ]);
                        }
                    }
                }
            }

            // Sync parent purchase_order_cns status
            $this->syncPoReceiveStatus($gr->po_no);

            return $gr->fresh('details');
        });
    }

    /**
     * Synchronize Status_Receive of purchase_order_cns based on its details.
     */
    protected function syncPoReceiveStatus(string $poNo, ?string $grNo = null, ?string $grDate = null): void
    {
        $details = DB::table('purchase_order_cns_dtl')->where('PO_No', $poNo)->get();
        if ($details->isEmpty()) {
            return;
        }

        $totalOrdered  = 0.0;
        $totalReceived = 0.0;
        $totalRemain   = 0.0;

        foreach ($details as $dtl) {
            $ord = (float) ($dtl->Qty ?? 0);
            $rec = (float) ($dtl->Qty_Receive ?? 0);
            $totalOrdered += $ord;
            $totalReceived += $rec;
            $totalRemain += max(0, $ord - $rec);
        }

        if ($totalReceived <= 0) {
            $statusReceive = 'WAITING';
        } elseif ($totalRemain <= 0 && $totalReceived >= $totalOrdered) {
            $statusReceive = 'RECEIVED';
        } else {
            $statusReceive = 'PARTIAL';
        }

        $updateData = ['Status_Receive' => $statusReceive];
        if ($grNo) {
            $updateData['GR_No'] = $grNo;
        }
        if ($grDate) {
            $updateData['GR_Date'] = $grDate;
            $updateData['Received_Date'] = $grDate;
        }

        DB::table('purchase_order_cns')->where('PO_No', $poNo)->update($updateData);
    }
}
