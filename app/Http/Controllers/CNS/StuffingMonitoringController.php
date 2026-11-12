<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CNS\StuffingContainerRequest;
use App\Services\CNS\StuffingContainerService;
use App\Services\CNS\StuffingNoteService;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StuffingMonitoringController extends Controller
{
    protected $containers;
    protected $notes;

    public function __construct(StuffingContainerService $containers, StuffingNoteService $notes)
    {
        $this->containers = $containers;
        $this->notes = $notes;
    }

    public function indexStuffingMonitoring()
    {
        $vss = VesselSchedule::orderBy('Order_No')->get();

        return view('cns.export.stuffing-monitoring', compact('vss'));
    }

    public function indexStuffingMonitoringXml(Request $request)
    {
        if (!headers_sent()) {
            if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
                header("Content-type: application/xhtml+xml");
            } else {
                header("Content-type: text/xml");
            }
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $vessels = VesselScheduleDtl::active()
            ->whereBetween('Date', [$request->tgl1, $request->tgl2])
            ->get();

        $vesselIdsByOrder = VesselSchedule::whereIn('Order_No', $vessels->pluck('Order_No')->filter()->unique()->values())
            ->pluck('id', 'Order_No');
        $noteMetadata = $this->notes->metadataByScheduleIds($vesselIdsByOrder->values()->all());

        foreach ($vessels as $key => $vessel) {
            $tare = (float) $vessel->Tare;
            $nettoKg = (float) $vessel->Netto_KG;
            $nettoMt = round($nettoKg / 1000, 2);
            $grossKg = $tare + $nettoKg;
            $grossMt = round($grossKg / 1000, 2);
            $vesselId = $vesselIdsByOrder->get($vessel->Order_No);
            $hasNote = $vesselId && isset($noteMetadata[(string) $vesselId]);

            echo "<row id=\"$vessel->ID\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $vessel->Order_No) . "</cell>";
            echo "<cell>" . htmlspecialchars(date("H:i", strtotime($vessel->Time_From))) . "</cell>";
            echo "<cell>" . htmlspecialchars(date("H:i", strtotime($vessel->Time_To))) . "</cell>";
            echo "<cell>" . htmlspecialchars(date("d M Y", strtotime($vessel->Date))) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $vessel->Police_No) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $vessel->Container) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $vessel->Seal) . "</cell>";
            echo "<cell>" . htmlspecialchars(number_format($tare, 2, '.', ',')) . "</cell>";
            echo "<cell>" . htmlspecialchars(number_format($nettoKg, 2, '.', ',')) . "</cell>";
            echo "<cell>" . htmlspecialchars(number_format($nettoMt, 2, '.', ',')) . "</cell>";
            echo "<cell>" . htmlspecialchars(number_format($grossKg, 2, '.', ',')) . "</cell>";
            echo "<cell>" . htmlspecialchars(number_format($grossMt, 2, '.', ',')) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $vessel->Note) . "</cell>";
            echo "<cell>" . ($hasNote ? 'Ada' : 'Tidak Ada') . "</cell>";
            echo "</row>";
        }

        echo "</rows>";
    }

    public function saveStuffingMonitoring(StuffingContainerRequest $request)
    {
        $request->validate([
            'Order_No' => 'required|string|exists:vessel_schedules,Order_No',
        ]);

        $schedule = $this->containers->scheduleForOrder((string) $request->input('Order_No'));

        if (!$schedule) {
            return response()->json(['status' => 'error', 'message' => 'Vessel schedule not found'], 404);
        }

        try {
            $container = null;

            DB::transaction(function () use ($request, $schedule, &$container) {
                $container = $this->containers->save(
                    $schedule,
                    $request->all(),
                    $request->filled('id') ? (int) $request->input('id') : null
                );

                if ($request->hasFile('Weighbridge_Note')) {
                    $this->notes->replace($schedule, $request->file('Weighbridge_Note'));
                }
            });
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => $request->filled('id') ? 'Container updated successfully' : 'Container created successfully',
            'id' => $container ? $container->ID : null,
        ]);
    }

    public function deleteStuffingMonitoring(Request $request)
    {
        $request->validate(['id' => 'required|integer']);

        $container = VesselScheduleDtl::active()->where('ID', $request->input('id'))->first();

        if (!$container) {
            return response()->json(['status' => 'error', 'message' => 'Container not found'], 404);
        }

        $schedule = VesselSchedule::where('Order_No', $container->Order_No)->orderBy('id')->first();

        if (!$schedule) {
            return response()->json(['status' => 'error', 'message' => 'Vessel schedule not found'], 404);
        }

        try {
            $this->containers->cancelMany($schedule, [$container->ID]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json(['status' => 'success', 'message' => 'Container deleted successfully']);
    }

    public function detailStuffingMonitoring(Request $request)
    {
        $request->validate(['ID' => 'required|integer']);

        $vsd = VesselScheduleDtl::active()->where('ID', $request->input('ID'))->first();

        if (!$vsd) {
            return response()->json(['status' => 'error', 'message' => 'Container not found'], 404);
        }

        $vs = VesselSchedule::where('Order_No', $vsd->Order_No)->orderBy('id')->first();
        $note = $vs ? $this->notes->forSchedule($vs) : null;

        return response()->json([
            'ID' => $vsd->ID,
            'Order_No' => $vsd->Order_No,
            'Time_From' => substr($vsd->Time_From, 0, 5),
            'Time_To' => substr($vsd->Time_To, 0, 5),
            'Date' => $vsd->Date,
            'Police_No' => $vsd->Police_No,
            'Container' => $vsd->Container,
            'Seal' => $vsd->Seal,
            'Tare' => (float) $vsd->Tare,
            'Netto_KG' => (float) $vsd->Netto_KG,
            'Note' => $vsd->Note,
            'Note_Url' => $this->notes->url($note),
            'Note_Name' => $note ? $note->Filename : null,
        ]);
    }

    public function showWeighbridgeNote(Request $request)
    {
        $note = $this->notes->forOrder((string) $request->query('Order_No'));

        if (!$note) {
            abort(404, 'Weighbridge note not found');
        }

        $path = public_path('filevendor/' . $note->Path);

        if (!is_file($path)) {
            abort(404, 'Weighbridge note file not found');
        }

        return response()->file($path);
    }

    public function weighbridgeNoteInfo(Request $request)
    {
        $note = $this->notes->forOrder((string) $request->query('Order_No'));

        return response()->json([
            'url' => $this->notes->url($note),
            'name' => $note ? $note->Filename : null,
        ]);
    }
}
