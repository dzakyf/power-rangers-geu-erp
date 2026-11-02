<?php

namespace App\Services\CNS;

use App\Services\UploadService;
use App\VesselSchedule;
use Illuminate\Support\Facades\DB;

class StuffingNoteService
{
    public const TOPIC = 'stuffing-weighbridge-note';

    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function forSchedule($schedule): ?object
    {
        $scheduleId = $schedule instanceof VesselSchedule ? $schedule->id : $schedule;

        return DB::table('meta_files')
            ->where('Topic', self::TOPIC)
            ->where('Map_To', (string) $scheduleId)
            ->orderByDesc('ID')
            ->first();
    }

    public function forOrder(string $orderNo): ?object
    {
        $schedule = VesselSchedule::where('Order_No', $orderNo)->orderBy('id')->first();

        return $schedule ? $this->forSchedule($schedule) : null;
    }

    public function replace(VesselSchedule $schedule, $file): void
    {
        $oldNote = $this->forSchedule($schedule);

        if ($oldNote) {
            $oldPath = public_path('filevendor/' . $oldNote->Path);

            if (is_file($oldPath)) {
                @unlink($oldPath);
            }

            DB::table('meta_files')->where('ID', $oldNote->ID)->delete();
        }

        $this->uploadService->storeFile(
            $file,
            'file',
            self::TOPIC,
            (string) $schedule->id,
            'Weighbridge Note'
        );
    }

    public function url($note): ?string
    {
        return $note ? asset('filevendor/' . $note->Path) : null;
    }

    public function metadataByScheduleIds(array $scheduleIds): array
    {
        if (empty($scheduleIds)) {
            return [];
        }

        $metadata = [];
        $rows = DB::table('meta_files')
            ->where('Topic', self::TOPIC)
            ->whereIn('Map_To', array_map('strval', $scheduleIds))
            ->orderByDesc('ID')
            ->get();

        foreach ($rows as $row) {
            if (!isset($metadata[(string) $row->Map_To])) {
                $metadata[(string) $row->Map_To] = $row;
            }
        }

        return $metadata;
    }
}
