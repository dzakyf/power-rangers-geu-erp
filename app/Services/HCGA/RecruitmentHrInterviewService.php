<?php

namespace App\Services\HCGA;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RecruitmentHrInterviewService
{
    /**
     * Daftar 6 field Pertanyaan Wajib yang dikelola pada tahap Interview HR.
     *
     * @var array
     */
    public const MANDATORY_QUESTION_FIELDS = [
        'Last_Salary',
        'Salary_Expectation',
        'Usia',
        'Domisili',
        'Computer',
        'Notice_Period',
    ];

    /**
     * Ambil daftar antrean kandidat untuk tahap Interview HR (Status = INTERVIEW_HR).
     *
     * @param string|null $location
     * @return \Illuminate\Support\Collection
     */
    public function getCandidateList(?string $location = null)
    {
        return DB::table('recruitment')
            ->where(function ($q) use ($location) {
                if (!empty($location) && $location !== 'ALL') {
                    $q->where('Location', $location);
                }
            })
            ->where('Status', 'INTERVIEW_HR')
            ->orderBy('ID', 'DESC')
            ->get();
    }

    /**
     * Ambil detail kandidat beserta data recruitment_detail dan requisition.
     *
     * @param int|string $id
     * @return array|null
     */
    public function getCandidateDetail($id): ?array
    {
        if (empty($id)) {
            return null;
        }

        $datahasil = DB::table('recruitment')->where('ID', $id)->first();
        if (!$datahasil) {
            return null;
        }

        $datahasil2 = null;
        if (Schema::hasTable('recruitment_detail')) {
            $datahasil2 = DB::table('recruitment_detail')
                ->where('ID', $datahasil->ID)
                ->orWhere('Task_ID', $datahasil->Task_ID)
                ->first();
        }

        $requisition = null;
        if (!empty($datahasil->Requisition_ID)) {
            $requisition = DB::table('requisition')->where('Task_ID', $datahasil->Requisition_ID)->first();
        }

        return [
            'datahasil'   => $datahasil,
            'datahasil2'  => $datahasil2,
            'requisition' => $requisition,
            'id'          => $id,
        ];
    }

    /**
     * Simpan keputusan Interview HR dan 6 Pertanyaan Wajib ke database.
     *
     * @param array $payload
     * @param string $currentUser
     * @return array
     */
    public function saveDecision(array $payload, string $currentUser): array
    {
        $id = $payload['ID'] ?? null;
        if (!$id) {
            return [
                'success' => false,
                'message' => 'ID kandidat tidak ditemukan',
            ];
        }

        $interviewStatus = $payload['Interview_Status'] ?? '';

        if ($interviewStatus === 'ACCEPT') {
            $statusnya = 'INTERVIEW_USER';
        } elseif ($interviewStatus === 'PENDING') {
            $statusnya = 'PENDING';
        } else {
            $statusnya = $interviewStatus ?: 'REJECT';
        }

        $recruitmentUpdate = [
            'Interview_File'   => $payload['Interview_File'] ?? '',
            'Interview_By'     => $currentUser,
            'Interview_Date'   => !empty($payload['Interview_Date']) ? $payload['Interview_Date'] : date('Y-m-d'),
            'Interview_Status' => $interviewStatus,
            'Interview_Note'   => $payload['Interview_Note'] ?? '',
            'Status'           => $statusnya,
        ];

        if (array_key_exists('Interview_Result', $payload)) {
            $recruitmentUpdate['Interview_Result'] = $payload['Interview_Result'] ?? '';
        }

        if (Schema::hasColumn('recruitment', 'Modified_By')) {
            $recruitmentUpdate['Modified_By'] = $currentUser;
        }
        if (Schema::hasColumn('recruitment', 'Modified_Date')) {
            $recruitmentUpdate['Modified_Date'] = now();
        }

        DB::beginTransaction();
        try {
            DB::table('recruitment')->where('ID', $id)->update($recruitmentUpdate);

            if (Schema::hasTable('recruitment_detail')) {
                $candidate = DB::table('recruitment')->where('ID', $id)->first();
                $detailUpdate = [];

                foreach (self::MANDATORY_QUESTION_FIELDS as $field) {
                    if (array_key_exists($field, $payload) && Schema::hasColumn('recruitment_detail', $field)) {
                        $detailUpdate[$field] = $payload[$field];
                    }
                }

                if (!empty($detailUpdate) && $candidate) {
                    $existingDetail = DB::table('recruitment_detail')
                        ->where('ID', $candidate->ID)
                        ->orWhere('Task_ID', $candidate->Task_ID)
                        ->first();

                    if ($existingDetail) {
                        DB::table('recruitment_detail')->where('ID', $existingDetail->ID)->update($detailUpdate);
                    } else {
                        $detailUpdate['ID'] = $candidate->ID;
                        $detailUpdate['Task_ID'] = $candidate->Task_ID;
                        DB::table('recruitment_detail')->insert($detailUpdate);
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Keputusan Wawancara Berhasil Disimpan',
                'status'  => $statusnya,
            ];
        } catch (Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Gagal menyimpan keputusan: ' . $e->getMessage(),
            ];
        }
    }
}
