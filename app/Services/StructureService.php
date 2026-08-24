<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StructureService
{
    public function getDirectUpper(string $nik)
    {
        $auth = DB::table('users')->where("Emp_ID", $nik)->first();
        $pos = DB::table('master_position')->where("Initial", $auth->Position)->first();
        if (!$pos) {
            return null;
        }

        // 3. Find the manager holding that position
        return DB::table('organization_structure as org')
            ->where(function ($query) use ($pos) {
                for ($i = $pos->Level - 1; $i >= 1; $i--) {
                    $query->orWhere("org.Level{$i}", 1);
                }
            })
            ->where('org.NIK', '!=', $nik)
            ->where("Department", $pos->Department)
            ->select(
                'org.NIK as upper_nik',
                'org.Name as upper_name',
            )
            ->first();
    }

    public function getDirectLower(string $nik)
    {
        $auth = DB::table('users')->where("Emp_ID", $nik)->first();
        $pos = DB::table('master_position')->where("Initial", $auth->Position)->first();
        if (!$pos) {
            return null;
        }

        // 3. Find the manager holding that position
        return DB::table('organization_structure as org')
            ->where(function ($query) use ($pos) {
                for ($i = $pos->Level; $i >= 8; $i++) {
                    $query->orWhere("org.Level{$i}", 1);
                }
            })
            ->where('org.NIK', '!=', $nik)
            ->where("Department", $pos->Department)
            ->select(
                'org.NIK as lower_nik',
                'org.Name as lower_name',
            )
            ->first();
    }

    public function getUpper(string $nik)
    {
        $auth = DB::table('users')->where("Emp_ID", $nik)->first();
        $pos = DB::table('master_position')->where("Initial", $auth->Position)->first();
        if (!$pos) {
            return null;
        }

        // 3. Find the manager holding that position
        return DB::table('organization_structure as org')
            ->where(function ($query) use ($pos) {
                for ($i = $pos->Level - 1; $i >= 1; $i--) {
                    $query->orWhere("org.Level{$i}", 1);
                }
            })
            ->where('org.NIK', '!=', $nik)
            ->where("Department", $pos->Department)
            ->select(
                'org.NIK as upper_nik',
                'org.Name as upper_name',
            )
            ->get();
    }

    public function getLower(string $nik)
    {
        $auth = DB::table('users')->where("Emp_ID", $nik)->first();
        $pos = DB::table('master_position')->where("Initial", $auth->Position)->first();
        if (!$pos) {
            return null;
        }

        // 3. Find the manager holding that position
        return DB::table('organization_structure as org')
            ->where(function ($query) use ($pos) {
                for ($i = $pos->Level; $i >= 8; $i++) {
                    $query->orWhere("org.Level{$i}", 1);
                }
            })
            ->where('org.NIK', '!=', $nik)
            ->where("Department", $pos->Department)
            ->select(
                'org.NIK as lower_nik',
                'org.Name as lower_name',
            )
            ->get();
    }

    public function isUpper(string $nik)
    {
        $hashLower = $this->getLower($nik);
        return count($hashLower) > 0 ? true : false;
    }
}
