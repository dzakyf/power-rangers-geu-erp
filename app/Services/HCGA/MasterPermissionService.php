<?php

namespace App\Services\HCGA;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class MasterPermissionService
{
    /**
     * Ambil query semua kategori izin beserta nama jenis izinnya
     */
    public function getCategories()
    {
        return DB::table('kategori_izin as k')
            ->leftJoin('jns_izin as j', 'j.izin_jenis_id', '=', 'k.izin_jenis_id')
            ->select('k.*', 'j.izin_jenis_name')
            ->orderBy('k.kat_izin_id', 'ASC')
            ->get();
    }

    /**
     * Ambil master jenis izin untuk dropdown / referensi
     */
    public function getJenisIzinList()
    {
        return DB::table('jns_izin')->orderBy('izin_jenis_id', 'ASC')->get();
    }

    /**
     * Update baris kategori izin dari dhtmlx DataProcessor
     */
    public function updateCategoryRow($rowId, array $param): string
    {
        $code = trim($param[$rowId . "_c1"] ?? '');
        $name = trim($param[$rowId . "_c2"] ?? '');
        $group = (int) ($param[$rowId . "_c3"] ?? 1);
        $days = (int) ($param[$rowId . "_c4"] ?? 0);
        $deduct = strtoupper(trim($param[$rowId . "_c5"] ?? 'NO'));
        $attachment = strtoupper(trim($param[$rowId . "_c6"] ?? 'NO'));
        $status = strtoupper(trim($param[$rowId . "_c7"] ?? 'ACTIVE'));

        if (!in_array($deduct, ['YES', 'NO'])) {
            $deduct = 'NO';
        }
        if (!in_array($attachment, ['YES', 'NO'])) {
            $attachment = 'NO';
        }
        if (!in_array($status, ['ACTIVE', 'INACTIVE'])) {
            $status = 'ACTIVE';
        }

        DB::table('kategori_izin')
            ->where('kat_izin_id', $rowId)
            ->update([
                'kat_izin_code'          => $code ?: null,
                'kat_izin_nama'          => $name,
                'izin_jenis_id'          => $group,
                'jatah_hari'             => $days,
                'is_deduct_annual_leave' => $deduct,
                'is_need_attachment'     => $attachment,
                'status'                 => $status,
            ]);

        return "updated";
    }

    /**
     * Insert baris kategori izin dari dhtmlx DataProcessor
     */
    public function insertCategoryRow($rowId, array $param): string
    {
        $code = trim($param[$rowId . "_c1"] ?? '');
        $name = trim($param[$rowId . "_c2"] ?? '');
        $group = (int) ($param[$rowId . "_c3"] ?? 1);
        $days = (int) ($param[$rowId . "_c4"] ?? 0);
        $deduct = strtoupper(trim($param[$rowId . "_c5"] ?? 'NO'));
        $attachment = strtoupper(trim($param[$rowId . "_c6"] ?? 'NO'));
        $status = strtoupper(trim($param[$rowId . "_c7"] ?? 'ACTIVE'));

        if (!in_array($deduct, ['YES', 'NO'])) {
            $deduct = 'NO';
        }
        if (!in_array($attachment, ['YES', 'NO'])) {
            $attachment = 'NO';
        }
        if (!in_array($status, ['ACTIVE', 'INACTIVE'])) {
            $status = 'ACTIVE';
        }

        // Jika code tidak diisi, buat slug sederhana dari nama
        if (empty($code) && !empty($name)) {
            $code = strtoupper(preg_replace('/[^A-Za-z0-9_]/', '_', trim($name)));
        }

        DB::table('kategori_izin')->insert([
            'kat_izin_code'          => $code ?: null,
            'kat_izin_nama'          => $name,
            'izin_jenis_id'          => $group,
            'jatah_hari'             => $days,
            'is_deduct_annual_leave' => $deduct,
            'is_need_attachment'     => $attachment,
            'status'                 => $status,
        ]);

        return "inserted";
    }

    /**
     * Delete baris kategori izin dari dhtmlx DataProcessor
     */
    public function deleteCategoryRow($rowId): string
    {
        DB::table('kategori_izin')->where('kat_izin_id', $rowId)->delete();
        return "deleted";
    }
}

