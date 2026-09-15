<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Align the profit/loss report template direct-cost lines (group 502) with the
 * COA (master_warehouse_account): 52001 Bensin, 52002 Charging, 52003 Parkir.
 * The report renders one row per Code, so codes must match the COA exactly.
 */
class AlignDirectCostLinesToCoa extends Migration
{
    private const COA = [
        '52001' => 'Bensin',
        '52002' => 'Charging',
        '52003' => 'Parkir',
    ];

    public function up()
    {
        foreach (['booth', 'mobil'] as $type) {
            $this->ensureLines($type);
            $this->renumber($type);
        }
    }

    private function ensureLines(string $type): void
    {
        foreach (self::COA as $code => $name) {
            $existing = DB::table('master_warehouse_profit_loss')
                ->where('type', $type)
                ->where('Code', $code)
                ->where('Rumus', '502')
                ->first();

            if ($existing) {
                DB::table('master_warehouse_profit_loss')
                    ->where('ID', $existing->ID)
                    ->update(['Description' => $name]);
                continue;
            }

            DB::table('master_warehouse_profit_loss')->insert([
                'type'        => $type,
                'Judul'       => null,
                'Description' => $name,
                'Code'        => $code,
                'Rumus'       => '502',
                'Normal'      => 'NORMAL',
                'Urut'        => null,
                'Unit_Name'   => 'Rupiah',
                'Level'       => null,
                'Indentation' => 40,
                'Bold'        => null,
            ]);
        }
    }

    private function renumber(string $type): void
    {
        $rows = DB::table('master_warehouse_profit_loss')
            ->where('type', $type)
            ->orderBy('Urut')
            ->orderBy('ID')
            ->get();

        $headerId = null;
        foreach ($rows as $r) {
            if ((string) $r->Judul === '502') {
                $headerId = $r->ID;
                break;
            }
        }

        $direct = $rows->filter(fn ($r) => (string) $r->Rumus === '502')
            ->sortBy('Code')
            ->values()
            ->all();

        $ordered = [];
        foreach ($rows as $r) {
            if ((string) $r->Rumus === '502') {
                continue; // re-inserted right after the "Biaya Langsung" header
            }
            $ordered[] = $r;
            if ($headerId !== null && $r->ID === $headerId) {
                foreach ($direct as $d) {
                    $ordered[] = $d;
                }
            }
        }

        // Fallback: header not found -> append the direct lines at the end
        if ($headerId === null) {
            foreach ($direct as $d) {
                $ordered[] = $d;
            }
        }

        $urut = 1;
        foreach ($ordered as $r) {
            DB::table('master_warehouse_profit_loss')->where('ID', $r->ID)->update(['Urut' => $urut++]);
        }
    }

    public function down()
    {
        // Best-effort revert: remove the inserted Parkir (52003) lines and
        // restore the previous descriptions.
        foreach (['booth', 'mobil'] as $type) {
            DB::table('master_warehouse_profit_loss')->where('type', $type)->where('Code', '52003')->where('Rumus', '502')->delete();
            DB::table('master_warehouse_profit_loss')->where('type', $type)->where('Code', '52002')->where('Rumus', '502')->update(['Description' => 'Parkir']);
            DB::table('master_warehouse_profit_loss')->where('type', 'mobil')->where('Code', '52001')->where('Rumus', '502')->update(['Description' => 'Charging']);
            $this->renumber($type);
        }
    }
}
