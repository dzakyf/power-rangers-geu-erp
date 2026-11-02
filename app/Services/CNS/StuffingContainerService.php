<?php

namespace App\Services\CNS;

use App\ShipmentInstruction;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use App\VesselScheduleLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StuffingContainerService
{
    public const STATUS_ACTIVE = 'CLOSED';
    public const STATUS_CANCELED = 'CANCELED';

    public function scheduleForOrder(string $orderNo): ?VesselSchedule
    {
        return VesselSchedule::where('Order_No', $orderNo)->orderBy('id')->first();
    }

    public function findForSchedule(VesselSchedule $schedule, int $containerId): ?VesselScheduleDtl
    {
        return VesselScheduleDtl::active()
            ->forSchedule($schedule)
            ->where('ID', $containerId)
            ->first();
    }

    public function save(VesselSchedule $schedule, array $data, ?int $containerId = null): VesselScheduleDtl
    {
        $container = $containerId
            ? $this->findForSchedule($schedule, $containerId)
            : null;

        if ($containerId && !$container) {
            throw ValidationException::withMessages([
                'id' => 'Container tidak ditemukan pada vessel schedule ini.',
            ]);
        }

        if (!$container) {
            $container = new VesselScheduleDtl();
            $container->Created_By = $this->actor();
            $container->Created_Date = now();
        }

        $container->Updated_By = $this->actor();
        $container->Updated_Date = now();

        $tare = (float) ($data['Tare'] ?? 0);
        $nettoKg = (float) ($data['Netto_KG'] ?? 0);

        $container->Task_ID = $schedule->Task_ID;
        $container->Order_No = $schedule->Order_No;
        $container->Time_From = $this->normalizeTime($data['Time_From'] ?? null);
        $container->Time_To = $this->normalizeTime($data['Time_To'] ?? null);
        $container->Date = $data['Date'];
        $container->Police_No = (string) ($data['Police_No'] ?? '');
        $container->Container = (string) $data['Container'];
        $container->Seal = (string) ($data['Seal'] ?? '');
        $container->Tare = $tare;
        $container->Netto_KG = $nettoKg;
        $container->Netto_MT = round($nettoKg / 1000, 2);
        $container->Gross_KG = $tare + $nettoKg;
        $container->Gross_MT = round(($tare + $nettoKg) / 1000, 2);
        $container->Note = (string) ($data['Note'] ?? '');
        $container->Status = self::STATUS_ACTIVE;
        $container->save();

        $this->writeScheduleLog($schedule, 'STUFFING CONTAINER SAVED', $container->Container . ' (' . $container->Seal . ')');

        return $container;
    }

    public function cancelMany(VesselSchedule $schedule, array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            throw ValidationException::withMessages([
                'ids' => 'Pilih container yang ingin dihapus.',
            ]);
        }

        $containers = VesselScheduleDtl::active()
            ->forSchedule($schedule)
            ->whereIn('ID', $ids)
            ->get();

        if ($containers->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'ids' => 'Sebagian container tidak ditemukan pada vessel schedule ini.',
            ]);
        }

        $siNos = $containers->pluck('SI_No')->filter()->unique()->values()->all();
        $summary = $containers->map(function ($container) {
            return $container->Container . ' (' . $container->Seal . ')';
        })->implode(', ');

        DB::transaction(function () use ($containers, $siNos, $summary, $schedule) {
            foreach ($containers as $container) {
                $container->Status = self::STATUS_CANCELED;
                $container->SI_No = null;
                $container->Updated_By = $this->actor();
                $container->Updated_Date = now();
                $container->save();
            }

            $this->syncShipmentInstructions($siNos);
            $this->writeScheduleLog($schedule, 'STUFFING CONTAINER CANCELED', $summary);
        });

        return [
            'count' => $containers->count(),
            'si_nos' => $siNos,
        ];
    }

    public function writeScheduleLog(VesselSchedule $schedule, string $status, string $containerSummary = ''): void
    {
        $log = $schedule->toArray();
        $log['Last_ID'] = $log['id'];
        unset($log['id']);
        $log['Action_By'] = $this->actor();
        $log['Action_Date'] = now();
        $log['Action_Status'] = $status;
        $log['Container'] = $containerSummary;

        try {
            VesselScheduleLog::create($log);
        } catch (\Throwable $e) {
            // Logging must not block container operations.
        }
    }

    private function syncShipmentInstructions(array $siNos): void
    {
        if (empty($siNos)) {
            return;
        }

        $instructions = ShipmentInstruction::whereIn('SI_No', $siNos)->get();

        foreach ($instructions as $instruction) {
            $hasActiveContainer = VesselScheduleDtl::active()
                ->where('SI_No', $instruction->SI_No)
                ->exists();

            if (!$hasActiveContainer) {
                $instruction->Status = 'DRAFT';
                $instruction->Updated_By = $this->actor();
                $instruction->Updated_Date = now();
                $instruction->save();
            }
        }
    }

    private function normalizeTime($value): string
    {
        if (!$value) {
            return '00:00:00';
        }

        return strlen((string) $value) === 5 ? $value . ':00' : (string) $value;
    }

    private function actor(): string
    {
        return auth()->check() ? (string) auth()->user()->username : 'system';
    }
}
