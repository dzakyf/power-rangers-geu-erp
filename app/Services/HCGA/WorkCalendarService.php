<?php

namespace App\Services\HCGA;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WorkCalendarService
{
    /**
     * Memproses payload dhtmlX DataProcessor untuk work_calendar_details.
     *
     * @param array $params
     * @param string $actor
     * @return array
     */
    public function processCalendarDetailsDataProcessor(array $params, string $actor = 'SYSTEM'): array
    {
        $results = [];
        $idsStr = $params['ids'] ?? '';
        if (empty($idsStr)) {
            return $results;
        }

        $rowIds = explode(',', $idsStr);

        DB::beginTransaction();
        try {
            foreach ($rowIds as $rowId) {
                $status = $params[$rowId . '_!nativeeditor_status'] ?? 'updated';
                $isWorking = isset($params[$rowId . '_c2']) ? (int) $params[$rowId . '_c2'] : 0;
                $defaultStart = !empty($params[$rowId . '_c3']) ? trim($params[$rowId . '_c3']) : null;
                $defaultEnd = !empty($params[$rowId . '_c4']) ? trim($params[$rowId . '_c4']) : null;
                $breakMinutes = isset($params[$rowId . '_c5']) ? (int) $params[$rowId . '_c5'] : 0;

                // Format time menjadi HH:MM:SS jika hanya HH:MM
                if ($defaultStart && strlen($defaultStart) === 5) {
                    $defaultStart .= ':00';
                }
                if ($defaultEnd && strlen($defaultEnd) === 5) {
                    $defaultEnd .= ':00';
                }

                if ($status === 'updated' || $status === 'inserted') {
                    DB::table('work_calendar_details')
                        ->where('ID', $rowId)
                        ->update([
                            'Is_Working'    => $isWorking,
                            'Default_Start' => $defaultStart,
                            'Default_End'   => $defaultEnd,
                            'Break_Minutes' => $breakMinutes,
                            'Updated_By'    => $actor,
                            'Updated_At'    => now(),
                        ]);

                    $results[] = [
                        'type' => 'update',
                        'sid'  => $rowId,
                        'tid'  => $rowId,
                    ];
                } elseif ($status === 'deleted') {
                    DB::table('work_calendar_details')->where('ID', $rowId)->delete();
                    $results[] = [
                        'type' => 'delete',
                        'sid'  => $rowId,
                        'tid'  => $rowId,
                    ];
                }
            }

            DB::commit();
            return $results;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Memproses payload dhtmlX DataProcessor untuk master_shifts.
     *
     * @param array $params
     * @param string $actor
     * @return array
     */
    public function processShiftsDataProcessor(array $params, string $actor = 'SYSTEM'): array
    {
        $results = [];
        $idsStr = $params['ids'] ?? '';
        if (empty($idsStr)) {
            return $results;
        }

        $rowIds = explode(',', $idsStr);

        DB::beginTransaction();
        try {
            foreach ($rowIds as $rowId) {
                $status = $params[$rowId . '_!nativeeditor_status'] ?? 'updated';
                $shiftCode = !empty($params[$rowId . '_c1']) ? strtoupper(trim($params[$rowId . '_c1'])) : 'SHIFT';
                $shiftName = !empty($params[$rowId . '_c2']) ? trim($params[$rowId . '_c2']) : 'Shift';
                $defaultStart = !empty($params[$rowId . '_c3']) ? trim($params[$rowId . '_c3']) : '08:00';
                $defaultEnd = !empty($params[$rowId . '_c4']) ? trim($params[$rowId . '_c4']) : '17:00';
                $breakMinutes = isset($params[$rowId . '_c5']) ? (int) $params[$rowId . '_c5'] : 60;
                $isCrossDay = isset($params[$rowId . '_c6']) ? (int) $params[$rowId . '_c6'] : 0;
                $isOff = isset($params[$rowId . '_c7']) ? (int) $params[$rowId . '_c7'] : 0;

                // Format time menjadi HH:MM:SS jika hanya HH:MM
                if ($defaultStart && strlen($defaultStart) === 5) {
                    $defaultStart .= ':00';
                }
                if ($defaultEnd && strlen($defaultEnd) === 5) {
                    $defaultEnd .= ':00';
                }

                if ($status === 'updated') {
                    DB::table('master_shifts')
                        ->where('id', $rowId)
                        ->update([
                            'shift_code'    => $shiftCode,
                            'shift_name'    => $shiftName,
                            'start_time'    => $defaultStart,
                            'end_time'      => $defaultEnd,
                            'break_minutes' => $breakMinutes,
                            'is_cross_day'  => $isCrossDay,
                            'is_off'        => $isOff,
                            'updated_by'    => $actor,
                            'updated_at'    => now(),
                        ]);

                    $results[] = [
                        'type' => 'update',
                        'sid'  => $rowId,
                        'tid'  => $rowId,
                    ];
                } elseif ($status === 'inserted') {
                    $newId = DB::table('master_shifts')->insertGetId([
                        'shift_code'    => $shiftCode,
                        'shift_name'    => $shiftName,
                        'start_time'    => $defaultStart,
                        'end_time'      => $defaultEnd,
                        'break_minutes' => $breakMinutes,
                        'is_cross_day'  => $isCrossDay,
                        'is_off'        => $isOff,
                        'status'        => 'ACTIVE',
                        'created_by'    => $actor,
                        'created_at'    => now(),
                    ]);

                    $results[] = [
                        'type' => 'insert',
                        'sid'  => $rowId,
                        'tid'  => $newId,
                    ];
                } elseif ($status === 'deleted') {
                    DB::table('master_shifts')->where('id', $rowId)->delete();
                    $results[] = [
                        'type' => 'delete',
                        'sid'  => $rowId,
                        'tid'  => $rowId,
                    ];
                }
            }

            DB::commit();
            return $results;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

