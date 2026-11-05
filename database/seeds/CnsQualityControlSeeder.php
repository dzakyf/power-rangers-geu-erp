<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CnsQualityControlSeeder extends Seeder
{
    /**
     * Seed 2 records for each Quality Control combination:
     * - Inbound  + Lab Internal (2 pemeriksaan)
     * - Inbound  + Lab External (2 pemeriksaan)
     * - Outbound + Lab Internal (2 pemeriksaan)
     * - Outbound + Lab External (2 pemeriksaan)
     *
     * Total: 8 header records x 4 detail rows (TOP, MIDDLE, BOTTOM, AVERAGE) = 32 detail rows.
     */
    public function run()
    {
        $hasHeaderTable = Schema::hasTable('cns_quality_controls');
        $hasDetailTable = Schema::hasTable('cns_quality_control_details');

        if (!$hasHeaderTable || !$hasDetailTable) {
            return;
        }

        $contract = null;
        $booking = null;
        if (Schema::hasTable('cns_buyer_contracts')) {
            $contract = DB::table('cns_buyer_contracts')->first();
            if ($contract && Schema::hasTable('cns_buyer_contract_bookings')) {
                $booking = DB::table('cns_buyer_contract_bookings')
                    ->where('Contract_ID', $contract->ID)
                    ->first();
            }
        }

        $combinations = [
            ['qc_type' => 'INBOUND',  'qc_category' => 'INTERNAL', 'date_offsets' => [1, 2]],
            ['qc_type' => 'INBOUND',  'qc_category' => 'EXTERNAL', 'date_offsets' => [3, 4]],
            ['qc_type' => 'OUTBOUND', 'qc_category' => 'INTERNAL', 'date_offsets' => [5, 6]],
            ['qc_type' => 'OUTBOUND', 'qc_category' => 'EXTERNAL', 'date_offsets' => [7, 8]],
        ];

        $levels = [
            'TOP'    => ['f_adj' => 0.00, 'm_adj' => 0.00],
            'MIDDLE' => ['f_adj' => 0.05, 'm_adj' => 0.04],
            'BOTTOM' => ['f_adj' => 0.10, 'm_adj' => 0.08],
        ];

        $today = Carbon::now();

        foreach ($combinations as $combo) {
            $qcType = $combo['qc_type'];
            $qcCategory = $combo['qc_category'];

            foreach ($combo['date_offsets'] as $idx => $offset) {
                $inspectionDate = $today->copy()->subDays($offset)->format('Y-m-d');
                $isOutbound = ($qcType === 'OUTBOUND');

                $prefixType = ($qcType === 'OUTBOUND') ? 'OUT' : 'IN';
                $prefixCat = ($qcCategory === 'INTERNAL') ? 'INT' : 'EXT';
                $datePart = Carbon::parse($inspectionDate)->format('Ymd');
                $seq = str_pad($idx + 1, 3, '0', STR_PAD_LEFT);
                $kodeQc = "QC/{$prefixType}/{$prefixCat}/{$datePart}/{$seq}";

                $headerPayload = [
                    'Kode_QC'         => $kodeQc,
                    'Inspection_Date' => $inspectionDate,
                    'QC_Type'         => $qcType,
                    'QC_Category'     => $qcCategory,
                    'Contract_ID'     => ($isOutbound && $contract) ? $contract->ID : null,
                    'Booking_ID'      => ($isOutbound && $booking) ? $booking->ID : null,
                    'Attachment_Path' => 'filesupplier/sample-coa.pdf',
                    'Notes'           => "Sample CoA {$qcCategory} {$qcType} Tanggal {$inspectionDate}",
                    'Is_Deleted'      => 0,
                    'Created_By'      => 'Seeder',
                    'updated_at'      => $today,
                ];

                $existingHeader = DB::table('cns_quality_controls')
                    ->where('Kode_QC', $kodeQc)
                    ->first();

                if ($existingHeader) {
                    DB::table('cns_quality_controls')
                        ->where('id', $existingHeader->id)
                        ->update($headerPayload);
                    $headerId = $existingHeader->id;
                } else {
                    $headerId = DB::table('cns_quality_controls')->insertGetId(array_merge($headerPayload, [
                        'created_at' => $inspectionDate . ' 09:00:00',
                    ]));
                }

                $basePalmitic = 4.10 + ($offset * 0.15);
                $baseOleic    = 3.60 + ($offset * 0.12);
                $baseMiu      = 1.10 + ($offset * 0.05);

                $paramSums = [
                    'FFA_As_Palmitic' => [],
                    'FFA_As_Oleic'    => [],
                    'MIU'             => [],
                    'Sulphur'         => [],
                    'IV'              => [],
                    'Total_Chlorides' => [],
                    'Phosphorus'      => [],
                    'Total_Metals'    => [],
                ];

                foreach ($levels as $level => $adj) {
                    $valPalmitic = round($basePalmitic + $adj['f_adj'], 2);
                    $valOleic    = round($baseOleic + $adj['f_adj'], 2);
                    $valMiu      = round($baseMiu + $adj['m_adj'], 2);
                    $valIv       = round(51.50 + $offset + ($adj['f_adj'] * 2), 2);
                    $valSulphur  = round(18.00 + ($offset * 0.5) + $adj['f_adj'], 2);
                    $valPhos     = round(7.50 + ($offset * 0.2) + $adj['f_adj'], 2);
                    $valChlor    = round(13.50 + ($offset * 0.3) + $adj['f_adj'], 2);
                    $valMetals   = round(2.20 + ($offset * 0.1) + $adj['f_adj'], 2);

                    $paramSums['FFA_As_Palmitic'][] = $valPalmitic;
                    $paramSums['FFA_As_Oleic'][]    = $valOleic;
                    $paramSums['MIU'][]             = $valMiu;
                    $paramSums['Sulphur'][]         = $valSulphur;
                    $paramSums['IV'][]              = $valIv;
                    $paramSums['Total_Chlorides'][] = $valChlor;
                    $paramSums['Phosphorus'][]      = $valPhos;
                    $paramSums['Total_Metals'][]    = $valMetals;

                    DB::table('cns_quality_control_details')->updateOrInsert(
                        [
                            'Quality_Control_ID' => $headerId,
                            'Sample_Level'       => $level,
                        ],
                        [
                            'FFA_As_Palmitic' => $valPalmitic,
                            'FFA_As_Oleic'    => $valOleic,
                            'MIU'             => $valMiu,
                            'Sulphur'         => $valSulphur,
                            'IV'              => $valIv,
                            'Total_Chlorides' => $valChlor,
                            'Phosphorus'      => $valPhos,
                            'Total_Metals'    => $valMetals,
                            'updated_at'      => $today,
                            'created_at'      => $inspectionDate . ' 09:00:00',
                        ]
                    );
                }

                // Average row
                $avgRow = [];
                foreach ($paramSums as $k => $vals) {
                    $avgRow[$k] = count($vals) > 0 ? round(array_sum($vals) / count($vals), 2) : null;
                }

                DB::table('cns_quality_control_details')->updateOrInsert(
                    [
                        'Quality_Control_ID' => $headerId,
                        'Sample_Level'       => 'AVERAGE',
                    ],
                    array_merge($avgRow, [
                        'updated_at' => $today,
                        'created_at' => $inspectionDate . ' 09:00:00',
                    ])
                );
            }
        }
    }
}
