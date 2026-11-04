<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;

class RecruitmentUserInterviewService
{
    /**
     * Ambil data antrean kandidat untuk tahap Interview User
     * Status yang valid: INTERVIEW_USER (atau fallback INTERVIEWED pada data legacy)
     *
     * @param string|null $location
     * @return \Illuminate\Support\Collection
     */
    public function getCandidateList(?string $location = null)
    {
        $query = DB::table("recruitment")
            ->where(function ($q) use ($location) {
                if (!empty($location) && $location !== 'ALL') {
                    $q->where("Location", $location);
                }
            })
            ->where(function ($q) {
                $q->where('Status', 'INTERVIEW_USER')
                  ->orWhere('Status', 'INTERVIEWED');
            })
            ->orderBy("ID", "DESC");

        return $query->get();
    }

    /**
     * Dapatkan detail kandidat lengkap beserta data kuesioner dan requisisi
     *
     * @param int|string $id
     * @return array|null
     */
    public function getCandidateDetail($id)
    {
        $datahasil = DB::table("recruitment")->where("ID", $id)->first();
        if (!$datahasil) {
            return null;
        }

        $datahasil2 = null;
        if (Schema::hasTable('recruitment_detail')) {
            $datahasil2 = DB::table("recruitment_detail")
                ->where("ID", $datahasil->ID)
                ->orWhere("Task_ID", $datahasil->Task_ID)
                ->first();
        }

        $requisition = null;
        if (!empty($datahasil->Requisition_ID)) {
            $requisition = DB::table("requisition")->where("Task_ID", $datahasil->Requisition_ID)->first();
        }

        return [
            'datahasil'   => $datahasil,
            'datahasil2'  => $datahasil2,
            'requisition' => $requisition,
            'id'          => $id,
        ];
    }

    /**
     * Simpan evaluasi dan keputusan interview user
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

        $userInterviewStatus = $payload['User_Interview_Status'] ?? '';

        // Tentukan alur status kandidat selanjutnya
        if ($userInterviewStatus === 'ACCEPT') {
            // Lanjut ke TECHNICAL_TEST atau BUSINESS_REVIEW
            $statusnya = 'TECHNICAL_TEST';
        } elseif ($userInterviewStatus === 'PENDING') {
            $statusnya = 'PENDING';
        } else {
            $statusnya = $userInterviewStatus ?: 'REJECT';
        }

        $recruitmentUpdate = [
            'User_Interview_File'   => $payload['User_Interview_File'] ?? '',
            'User_Interview_By'     => $currentUser,
            'User_Interview_Date'   => !empty($payload['User_Interview_Date']) ? $payload['User_Interview_Date'] : date('Y-m-d'),
            'User_Interview_Result' => $payload['User_Interview_Result'] ?? '',
            'User_Interview_Status' => $userInterviewStatus,
            'User_Interview_Note'   => $payload['User_Interview_Note'] ?? '',
            'Status'                => $statusnya,
        ];

        if (Schema::hasColumn('recruitment', 'Modified_By')) {
            $recruitmentUpdate['Modified_By'] = $currentUser;
        }
        if (Schema::hasColumn('recruitment', 'Modified_Date')) {
            $recruitmentUpdate['Modified_Date'] = now();
        }

        DB::beginTransaction();
        try {
            DB::table('recruitment')->where('ID', $id)->update($recruitmentUpdate);

            // Perbarui kuesioner pada recruitment_detail jika ada perubahan input
            if (Schema::hasTable('recruitment_detail')) {
                $candidate = DB::table('recruitment')->where('ID', $id)->first();
                $detailFields = [
                    'Describe_Self', 'Left_Job', 'Previous_Boss', 'Describe_Know',
                    'Describe_Improve', 'Describe_Looking', 'Describe_Interest',
                    'Describe_Long', 'Describe_Target', 'Salary_Expectation',
                    'Facility_Expectation', 'Environment', 'Free_Time',
                    'Describe_Strong', 'Describe_Weak', 'Overtime', 'Holiday',
                    'Shift_Work', 'Out_Town', 'Computer', 'Questions_Us'
                ];

                $detailUpdate = [];
                foreach ($detailFields as $field) {
                    if (array_key_exists($field, $payload)) {
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
                'message' => 'Hasil Interview User berhasil disimpan',
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

