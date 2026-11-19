<?php

namespace App\Services\HCGA;

use Illuminate\Support\Facades\DB;

/**
 * Service terpusat untuk sinkronisasi siklus hidup aset pada asset_list
 * dan pencatatan riwayat pemegang aset (asset_user_history).
 */
class AssetLifecycleService
{
    /**
     * Catat pemindahtanganan pemegang aset ke asset_user_history dan sinkronkan
     * User_Sebelumnya serta User_Responsible pada asset_list.
     */
    public function recordUserTransfer(
        string $assetNo,
        ?string $fromUser,
        string $toUser,
        string $sourceType = 'MANUAL_UPDATE',
        ?string $referenceNo = null,
        string $actor = 'system',
        ?string $note = null,
        ?string $effectiveDate = null,
        array $extraAssetUpdates = []
    ): void {
        $assetNo = trim($assetNo);
        $toUser = trim($toUser);
        if ($assetNo === '' || $toUser === '') {
            return;
        }

        DB::transaction(function () use ($assetNo, $fromUser, $toUser, $sourceType, $referenceNo, $actor, $note, $effectiveDate, $extraAssetUpdates) {
            $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
            if (!$asset) {
                return;
            }

            $prevUser = $fromUser !== null && trim($fromUser) !== ''
                ? trim($fromUser)
                : (trim((string) ($asset->User_Responsible ?? '')) ?: null);

            DB::table('asset_user_history')->insert([
                'Asset_No'       => $assetNo,
                'From_User'      => $prevUser,
                'To_User'        => $toUser,
                'Source_Type'    => $sourceType,
                'Reference_No'   => $referenceNo,
                'Effective_Date' => $effectiveDate ?: date('Y-m-d'),
                'Note'           => $note,
                'Created_By'     => $actor,
                'Created_At'     => now(),
            ]);

            $updateData = array_merge($extraAssetUpdates, [
                'User_Sebelumnya'    => $prevUser,
                'User_Responsible'   => $toUser,
                'Last_User'          => $actor,
                'Last_Modified_Date' => now(),
            ]);

            DB::table('asset_list')->where('ID', $asset->ID)->update($updateData);
        });
    }

    /**
     * Tandai aset sebagai DISPOSED dan isi Tanggal_Penghapusan tanpa menghapus record.
     */
    public function markDisposed(string $assetNo, ?string $disposalDate = null, string $actor = 'system'): void
    {
        $assetNo = trim($assetNo);
        if ($assetNo === '') {
            return;
        }

        DB::table('asset_list')
            ->where('Asset_No', $assetNo)
            ->update([
                'Status'              => 'DISPOSED',
                'Tanggal_Penghapusan' => ($disposalDate && $disposalDate !== '0000-00-00') ? $disposalDate : date('Y-m-d'),
                'Last_User'           => $actor,
                'Last_Modified_Date'  => now(),
            ]);
    }

    /**
     * Perbarui lokasi/departemen/gudang aset saat proses Asset Moving disetujui.
     */
    public function updateLocation(
        string $assetNo,
        ?string $newLocation,
        ?string $newDepartment = null,
        ?string $newWhId = null,
        string $actor = 'system'
    ): void {
        $assetNo = trim($assetNo);
        if ($assetNo === '') {
            return;
        }

        $update = [
            'Last_User'          => $actor,
            'Last_Modified_Date' => now(),
        ];

        if ($newLocation !== null && trim($newLocation) !== '') {
            $update['Asset_Location'] = trim($newLocation);
        }
        if ($newDepartment !== null && trim($newDepartment) !== '') {
            $update['Asset_Location_Department'] = trim($newDepartment);
        }
        if ($newWhId !== null && trim($newWhId) !== '') {
            $update['WH_ID'] = trim($newWhId);
        }

        DB::table('asset_list')->where('Asset_No', $assetNo)->update($update);
    }

    /**
     * Tandai aset mengalami kerusakan (RUSAK RINGAN / RUSAK BERAT) dari tiket/fixing.
     */
    public function markDamaged(string $assetNo, string $severity = 'RUSAK RINGAN', string $actor = 'system'): void
    {
        $assetNo = trim($assetNo);
        if ($assetNo === '') {
            return;
        }

        $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
        if (!$asset || strtoupper((string) ($asset->Status ?? '')) === 'DISPOSED') {
            return;
        }

        $normalizedSeverity = strtoupper(trim($severity)) === 'RUSAK BERAT' ? 'RUSAK BERAT' : 'RUSAK RINGAN';

        DB::table('asset_list')
            ->where('ID', $asset->ID)
            ->update([
                'Kondisi_Aset'       => $normalizedSeverity,
                'Last_User'          => $actor,
                'Last_Modified_Date' => now(),
            ]);
    }

    /**
     * Tandai aset sedang dalam proses perbaikan / maintenance.
     */
    public function markInMaintenance(string $assetNo, string $actor = 'system'): void
    {
        $assetNo = trim($assetNo);
        if ($assetNo === '') {
            return;
        }

        $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
        if (!$asset || strtoupper((string) ($asset->Status ?? '')) === 'DISPOSED') {
            return;
        }

        DB::table('asset_list')
            ->where('ID', $asset->ID)
            ->update([
                'Status'             => 'MAINTENANCE',
                'Last_User'          => $actor,
                'Last_Modified_Date' => now(),
            ]);
    }

    /**
     * Kembalikan status & kondisi aset menjadi ACTIVE & BAIK setelah perbaikan/tiket selesai.
     */
    public function markRestored(string $assetNo, string $actor = 'system'): void
    {
        $assetNo = trim($assetNo);
        if ($assetNo === '') {
            return;
        }

        $asset = DB::table('asset_list')->where('Asset_No', $assetNo)->first();
        if (!$asset || strtoupper((string) ($asset->Status ?? '')) === 'DISPOSED') {
            return;
        }

        DB::table('asset_list')
            ->where('ID', $asset->ID)
            ->update([
                'Status'             => 'ACTIVE',
                'Kondisi_Aset'       => 'BAIK',
                'Last_User'          => $actor,
                'Last_Modified_Date' => now(),
            ]);
    }
}
